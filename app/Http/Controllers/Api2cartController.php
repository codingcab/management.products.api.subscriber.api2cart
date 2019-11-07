<?php

namespace App\Http\Controllers;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class Api2cartController extends Controller
{
    const PRODUCT_ALLOWED_KEYS = [
        "id",
        "model",
        "special_price",
        "sprice_create",
        "sprice_expire",
        "quantity",
        "in_stock",
        "store_id"
    ];

    /**
     * @param $store_key
     * @param $product_data
     * @return false|string
     * @throws Exception
     */
    public static function updateProduct($store_key, $product_data)
    {
        $api_key = env('API2CART_API_KEY', '');

        if(empty($store_key)) {
            throw new Exception("Missing API2CART Store Key");
        }

        if(empty($api_key)) {
            throw new Exception("Missing API2CART Application Key");
        }

        if(empty($product_data)) {
            throw new Exception("Empty data received");
        }

        $guzzle = new Client([
            'base_uri' =>  'https://api.api2cart.com/v1.1/',
            'timeout' => 60,
            'exceptions' => true,
        ]);

        $response = $guzzle->post('product.update.json', [
            'query' => [
                'api_key' => $api_key,
                'store_key' => $store_key,
                ],
            'json' => Arr::only($product_data, self::PRODUCT_ALLOWED_KEYS)
        ]);

        $response_message = json_decode($response->getBody()->getContents(), true);

        if($response_message["return_code"] !== 0) {
            throw new Exception($response_message["return_message"], $response_message["return_code"]);
        }

    }
}
