<?php

namespace App\Http\Controllers;

use App\Api2cart\Products;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ProductsController extends BaseSnsController
{
    public function handleNotification($notification, $store_key)
    {
        $product_data = $this->generateProductData($notification);

        $api2cart_new = new Products($store_key);

        $response = $api2cart_new->updateOrCreate($product_data);

        if($response->isSuccess()) {
            Log::info('Product synced', $product_data);
            return $this->respond_ok_200();
        }

        Log::info('Product not updated, falling back to old method', [
            "sku" => $product_data["sku"],
            "response" => $response->jsonContent()
        ]);

        $api2cart = new \App\Http\Controllers\Api2Cart($store_key);

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
