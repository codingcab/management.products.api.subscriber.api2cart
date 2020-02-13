<?php

namespace App\Jobs;

use App\Api2cart\Products;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
     * @throws \Exception
     */
    public function handle()
    {
        $product = Products::getProductInfo($this->_store_key, $this->_product_data["sku"]);

        $context = [
            "expected" => $this->_product_data,
            "actual" => $product
        ];

        if($product) {
            info('Verify Product Sync', $context);
        } else {
            Log::alert("Verify Product Sync", $context);
        }

    }
}
