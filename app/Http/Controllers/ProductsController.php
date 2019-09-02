<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

class ProductsController extends BaseSnsController
{
    public function handleNotification($notification, $store_key)
    {
        Log::debug('SNS Notification received', $notification);

        $api_key = env('API2CART_API_KEY', 'API_KEY_NOT_SET');

        $api2cart = new \App\Http\Controllers\Api2Cart($api_key, $store_key);

        $api2cart->productUpdateOrCreate($notification);
    }
}
