<?php

namespace App\Jobs;

use App\Api2cart\Products;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
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
    private $_product_data = null;

    /**
     * Create a new job instance.
     *
     * @param string $store_key
     * @param array $product_data
     */
    public function __construct(string $store_key, array $product_data)
    {
        $this->_store_key = $store_key;
        $this->_product_data = $product_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $cache_key = $this->_store_key.'.'.$this->_product_data["sku"];

        $checksum = md5(Arr::query($this->_product_data));

        if(Cache::get($cache_key) === $checksum) {
            Log::info("Same update already pushed before, could be skipped but well... continue", $this->_product_data);
        }

        $response = Products::updateOrCreate($this->_store_key, $this->_product_data);

        if($response->isNotSuccess()) {
            Log::error('Could not update Product', $this->_product_data);
            Log::error('Received API2CART Response', $response->asArray());
            throw new Exception('Could not update Product');
        }

        info("SKU updated", $this->_product_data);

        Cache::put($cache_key, $checksum, 1440);

        // 1,10 will execute more less on 10% jobs
        // 1,100 will execute more less on 1% jobs
        // 1,500 will execute more less on 0.2% jobs
        // 1,1000 will execute more less on 0.1% jobs
        $random_int = random_int(1, env("PRODUCT_CHECK_THRESHOLD", 100));

        if($random_int <> 1) {
            VerifyProductSyncJob::dispatchNow($this->_store_key, $this->_product_data);
        }
    }

    public function failed(Exception $exception)
    {
        Log::error('Job failed', $this->_product_data);
    }

}
