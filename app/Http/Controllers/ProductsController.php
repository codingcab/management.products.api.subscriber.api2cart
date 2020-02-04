<?php

namespace App\Http\Controllers;

use App\Api2cart\Api2Cart_Product;
use Illuminate\Support\Arr;

class ProductsController extends BaseSnsController
{
    const RETURN_CODE_OK = 0;
    const RETURN_CODE_MODEL_NOT_FOUND = 112;

    public function handleNotification($notification, $store_key)
    {
        logger('Product update request', $notification);

        $product_data = $this->generateProductData($notification);

        $api2cart_new = new Api2Cart_Product($store_key);

        $response = $api2cart_new->updateProduct($product_data);

        if($response["return_code"] == self::RETURN_CODE_OK) {
            return $this->respond_ok_200();
        }

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
