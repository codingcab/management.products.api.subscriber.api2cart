<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

class SNSController extends Controller
{
    public function handleRequest(Request $request) {

        $requestJSON = json_decode($request->getContent(), true);

        if($requestJSON['Type'] == 'SubscriptionConfirmation') {
            return $this->subscribe($requestJSON);
        }

        $this->handleNotification($requestJSON);

    }

    private function subscribe($notification) {

        Log::info('Subscribing to topic');

        $guzzleClient = new \GuzzleHttp\Client();

        $guzzleResponse = $guzzleClient->get($notification['SubscribeURL']);

        Log::info('Successfully subscribed');

        Log::info($guzzleResponse);

        return app('Illuminate\Http\Response')->status();

    }

    function handleNotification($notification){

        $body = $notification['Body'];

        Log::info('Received message' , [$body]);

        $guzzlePostClient = new \GuzzleHttp\Client();

        $storekey = env('API2CART_STORE_KEY');
        $apikey = env('API2CART_KEY');

        $res = $guzzlePostClient->post("https://api.api2cart.com/v1.1/product.add.json?api_key=$apikey&store_key=$storekey&name=$body[ProductName]&model=$body[ProductModel]&description=$body[ProductDescription]&price=$body[Price]");

        Log::info($res->getBody());
        return $res->getBody();

    }
}
