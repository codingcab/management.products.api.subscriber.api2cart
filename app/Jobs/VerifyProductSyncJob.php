<?php

namespace App\Jobs;

use App\Api2cart\Products;
use App\Http\Kernel;
use Exception;
use Hamcrest\Thingy;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use function Aws\map;

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
        $store_id = Arr::has($this->_product_data, "store_id") ? $this->_product_data["store_id"] : null;

        $product_now = Products::getProductInfo($this->_store_key, $this->_product_data["sku"], $store_id);

        if(empty($product_now)) {
            Log::alert("Update Check FAILED - Could not find product", ["sku" => $this->_product_data["sku"]]);
            return;
        };

        $this->_results = [
            "type" => $product_now["type"],
            "sku" => $product_now["sku"],
            "store_id" => $store_id,
            "differences" => $this->getDifferences($this->_product_data, $product_now)
        ];

        if(empty($this->_results["differences"])) {
            info('Update Check OK', $this->_results);
        } else {
            Log::alert("Update Check FAILED", $this->_results);
        }

    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->_results;
    }

    /**
     * @param array $expected
     * @param array $actual
     * @return array
     */
    private function getDifferences(array $expected, array $actual): array
    {
        $keys_to_verify = [
            "price",
            "special_price",
            "quantity"
        ];

        $expected_data = Arr::only($expected, $keys_to_verify);

        $differences = [];

        foreach (array_keys($expected_data) as $key ) {
            if((!Arr::has($actual, $key)) or ($expected_data[$key] != $actual[$key])) {
                $differences[$key] = [
                    "expected" => $expected_data[$key],
                    "actual" => $actual[$key]
                ];
            }
        }

        return Arr::dot($differences);
    }

}
