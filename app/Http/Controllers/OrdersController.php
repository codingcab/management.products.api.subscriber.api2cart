<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class OrdersController extends SnsController
{
    //
    public function index($store_key) {

        $guzzle = new Client([
            'base_uri' =>  'https://api.api2cart.com/v1.1/',
            'timeout' => 60,
            'exceptions' => true,
        ]);

        $result = $guzzle->get(
            'orders.list.json',
                [
                    'query' => [
                        "api_key" => env('API2CART_API_KEY', ""),
                        "store_key" => $store_key
                    ]
                ]
            );


        return response()->json(
            $result->getBody()->getContents(),
            200
        );
    }

    /**
     * @inheritDoc
     */
    public function handleIncomingNotification(array $notification, string $store_key, int $store_id)
    {
        // TODO: Implement handleNotification() method.
    }
}
