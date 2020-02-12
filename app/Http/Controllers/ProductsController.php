<?php

namespace App\Http\Controllers;

use App\Api2cart\Products;
use App\Jobs\SyncProductJob;
use App\Jobs\VerifyProductSyncJob;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ProductsController extends SnsController
{
    /**
     * @param array $notification
     * @param string $store_key
     * @param int $store_id
     * @return JsonResponse
     * @throws Exception
     */
    public function handleIncomingNotification(array $notification, string $store_key, int $store_id)
    {
        $product_data = $this->generateProductData($notification);

        if (isset($store_id) && ($store_id != 0)) {
            $product_data['store_id'] = $store_id;
        }

        $this->dispatchSyncProductJob($store_key, $product_data);

        return $this->respond_200_OK();
    }

    /**
     * @param array $notification
     * @return array
     */
    private function generateProductData(array $notification)
    {
        $product = [];

        $product["sku"]             = $notification["sku"];
        $product["model"]           = $notification["sku"];
        $product["name"]            = $notification["name"];
        $product["description"]     = $notification["name"];
        $product["price"]           = $notification["price"];
        $product["special_price"]   = $notification["sale_price"];
        $product["sprice_create"]   = $notification["sale_price_start_date"];
        $product["sprice_expire"]   = $notification["sale_price_end_date"];

        $product["quantity"]        = intval($notification["quantity_available"]);

        if ($product["quantity"] > 0) {
            $product["in_stock"] = true;
        }

        if (Arr::has($notification, "store_id")) {
            $product["store_id"] = $notification["store_id"];
        }

        return $product;
    }

    /**
     * This function will randomly dispatch VerifyProductSyncJob with chain
     * idea is to verify some product updates
     *
     * @param string $store_key
     * @param array $product_data
     * @throws Exception
     */
    private function dispatchSyncProductJob(string $store_key, array $product_data): void
    {
        // 1,100 will execute more less on 1% jobs
        // 1,500 will execute more less on 0.2% jobs
        // 1,1000 will execute more less on 0.1% jobs
        $random_int = random_int(1,100);

        if($random_int <> 1) {
            SyncProductJob::dispatch($store_key, $product_data);
        } else {
            SyncProductJob::withChain([
                VerifyProductSyncJob::dispatch($store_key, $product_data)
            ])->dispatch($store_key, $product_data);
        }

    }
}
