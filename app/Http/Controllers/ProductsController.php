<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use function Psy\debug;

class ProductsController extends BaseSnsController
{
    public function handleNotification($notification, $store_key)
    {
        logger('Product update request', $notification);

        $api_key = env('API2CART_API_KEY', 'API_KEY_NOT_SET');

        $api2cart = new \App\Http\Controllers\Api2Cart($api_key, $store_key);

        $product_data = $notification;

        $product_data["quantity"] = $notification["quantity_available"];

        if($api2cart->productUpdateOrCreate($product_data)) {
            $this->respond_ok_200();
        }
    }
}
