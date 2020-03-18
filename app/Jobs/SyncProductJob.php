<?php

namespace App\Jobs;

use App\Api2cart\Products;
use App\Api2cart\RequestResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string|null
     */
    private $_store_key = null;

    /**
     * @var array|null
     */
    public $_product_data = null;

    /**
     * Create a new job instance.
     *
     * @param string $store_key
     * @param array $product_data
     */
    public function __construct(string $store_key, array $product_data)
    {
        $this->_store_key = $store_key;
        $this->_product_data = $this->convert($product_data);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle()
    {
        if ($this->isRepeatedUpdate()) {
            Log::info("Same update already pushed before, could be skipped but well... continue", $this->_product_data);
        };

        $response = Products::updateOrCreate($this->_store_key, $this->_product_data);

        if($response->isSuccess()) {
            info("SKU updated", $this->_product_data);
            $this->saveToCache();
            $this->verifyUpdate();
            return;
        }

        switch ($response->getReturnCode()) {
            case RequestResponse::RETURN_CODE_EXCEEDED_CONCURRENT_API_REQUESTS_PER_STORE:
                info('Exceeded concurrent API requests, pausing queue for 60 seconds');
                cache()->set('queue-paused', true, 60);
                break;

            default:
                Log::error('Update failed', [
                    'response' => $response->asArray(),
                    'data' => $this->_product_data
                ]);
                throw new Exception('Could not update Product');
                break;
        }
    }

    public function failed(Exception $exception)
    {
        Log::error('Job failed', $this->_product_data);
    }

    /**
     * @throws Exception
     */
    public function verifyUpdate()
    {
        if($this->shouldVerify()) {
            VerifyProductSyncJob::dispatchNow($this->_store_key, $this->_product_data);
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function shouldVerify()
    {
        // 1,10 will execute more less on 10% jobs
        // 1,1000 will execute more less on 0.1% jobs
        $random_int = random_int(1, env("PRODUCT_CHECK_THRESHOLD", 100));

        return $random_int == 1;
    }

    /**
     * @param array $data
     * @return array
     */
    private function convert(array $data)
    {
        $product = [];

        $product["sku"]             = $data["sku"];
        $product["model"]           = $data["sku"];
        $product["name"]            = $data["name"];
        $product["description"]     = $data["name"];
        $product["price"]           = $data["price"];
        $product["special_price"]   = $data["sale_price"];
        $product["sprice_create"]   = Carbon::createFromTimeString($data["sale_price_start_date"])->toDateString() . ' 00:00:00';
        $product["sprice_expire"]   = Carbon::createFromTimeString($data["sale_price_end_date"])->toDateString() . ' 00:00:00';
        $product["quantity"]        = intval($data["quantity_available"]);
        $product["store_id"]        = $data["store_id"];

        if ($product["quantity"] > 0) {
            $product["in_stock"] = "True";
        }

        return $product;
    }

    /**
     * @return void
     */
    public function saveToCache()
    {
        Cache::put($this->getCacheKey(), $this->getChecksum(), 60 * 24 * 7);
    }

    /**
     * @return boolean
     */
    private function isRepeatedUpdate()
    {
        $cache_key = $this->getCacheKey();

        $checksum = $this->getChecksum();

        return (Cache::get($cache_key) === $checksum);
    }

    /**
     * @return string
     */
    private function getCacheKey()
    {
        return implode('.', [
            $this->_store_key,
            $this->_product_data['sku']
        ]);
    }

    /**
     * @return string
     */
    private function getChecksum(): string
    {
        return md5(serialize($this->_product_data));
    }

}
