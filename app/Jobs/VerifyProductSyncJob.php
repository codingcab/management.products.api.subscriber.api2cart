<?php

namespace App\Jobs;

use App\Api2cart\Products;
use Exception;
use Hamcrest\Thingy;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Class VerifyProductSyncJob
 * @package App\Jobs
 */
class VerifyProductSyncJob implements ShouldQueue
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
     * @var array
     */
    private $_results = [];

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
        $keys_to_verify = [
            "price",
            "special_price",
            "quantity"
        ];

        $product_now = Products::getProductInfo($this->_store_key, $this->_product_data["sku"]);

        if(empty($product_now)) {
            Log::alert("Verify Product Failed - could not find product", ["sku" => $this->_product_data["sku"]]);
            return;
        };

        $this->_results["expected"]    = Arr::only($this->_product_data, $keys_to_verify);
        $this->_results["actual"]      = Arr::only($product_now, $keys_to_verify);
        $this->_results["difference"]  = array_diff($this->_results["expected"], $this->_results["actual"]);
        $this->_results["matching"]    = empty($this->_results["difference"]);

        $context = Arr::dot($this->getResults());

        if($this->getResults()["matching"]) {
            info('Product Sync Verification OK', $context);
        } else {
            Log::alert("Product Sync Verification Failed", $context);
        }

    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->_results;
    }

}
