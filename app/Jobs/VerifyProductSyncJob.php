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
        $product_now = Products::getProductInfo($this->_store_key, $this->_product_data["sku"]);

        if(empty($product_now)) {
            Log::alert("Update Check FAILED - Could not find product", ["sku" => $this->_product_data["sku"]]);
            return;
        };

        $this->_results = $this->compareValues($this->_product_data, $product_now);
        $this->_results['sku'] = $this->_product_data["sku"];

        $context = [
            "type" => $product_now["type"],
            "sku" => $product_now["sku"],
        ];

        if($this->getResults()["matching"]) {
            info('Update Check OK', $context);
        } else {
            Log::alert("Update Check FAILED", array_merge($context, $this->_results));
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
    private function compareValues(array $expected, array $actual): array
    {
        $keys_to_verify = [
            "price",
            "special_price",
            "quantity"
        ];

        $expected_data = Arr::only($expected, $keys_to_verify);

        $actual_data = Arr::only($actual, $keys_to_verify);;

        $difference = array_diff($expected_data, $actual_data);

        // reverse arrays so it looks like this
        // [
        //  "price" => [
        //      "actual"   => 4,
        //      "expected" => 3
        //      ]
        // ]
        array_walk($expected_data, function (&$a, $b) {
            $a = ["expected" => $a];
        });

        array_walk($actual_data, function (&$a, $b) {
            $a = ["actual" => $a];
        });

        return array_merge(
            Arr::dot($expected_data),
            Arr::dot($actual_data),
            Arr::dot(["difference" => array_keys($difference)]),
            ["matching" => empty($difference)]
        );
    }

}
