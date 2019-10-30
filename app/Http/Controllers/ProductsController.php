<?php

namespace App\Http\Controllers;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use function Psy\debug;

class ProductsController extends BaseSnsController
{
    public function handleNotification($notification, $store_key)
    {
        logger('Product update request', $notification);

        $api_key = env('API2CART_API_KEY', 'API_KEY_NOT_SET');

        $api2cart = new \App\Http\Controllers\Api2Cart($api_key, $store_key);

        $product_data = $this->generateProductData($notification);

        if($api2cart->productUpdateOrCreate($product_data)) {
            $this->respond_ok_200();
        }
    }

    /**
     * @param $notification
     * @return mixed
     */
    private function generateProductData($notification)
    {
        $product = [];

        $product["sku"]             = $notification["sku"];
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
