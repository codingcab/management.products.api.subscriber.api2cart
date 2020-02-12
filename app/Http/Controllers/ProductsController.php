<?php

namespace App\Http\Controllers;

use App\Api2cart\Products;
use App\Jobs\PushToApi2CartJob;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ProductsController extends SnsController
{
    /**
     * @param array $notification
     * @param string $store_key
     * @param int $store_id
     * @return mixed|void
     */
    public function handleNotification(array $notification, string $store_key, int $store_id)
    {
        $product_data = $this->generateProductData($notification);

        if (isset($store_id) && ($store_id != 0)) {
            $product_data['store_id'] = $store_id;
        }

        PushToApi2CartJob::dispatch($store_key, $product_data);
    }

    /**
     * @param $notification
     * @return mixed
     */
    private function generateProductData($notification)
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
}
