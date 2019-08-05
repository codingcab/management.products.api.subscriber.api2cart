<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductsController extends SNSController
{
    public function handleRequest(Request $request, $storekey) {

        $requestJSON = json_decode($request->getContent(), true);

        if($requestJSON['Type'] == 'SubscriptionConfirmation') {
            return $this->subscribe($requestJSON);
        }

        return $this->handleNotification($requestJSON, $storekey);

    }

    function handleNotification($notification, $storekey){

        $body = $notification['Body'];

        $guzzlePostClient = new \GuzzleHttp\Client();

        $url = $this->urlBuilder($body, $storekey);

        $res = $guzzlePostClient->post($url);

        $jsonRes = json_decode($res->getBody(), true);

        return response()->json($jsonRes['return_message'], $jsonRes['return_code']);

    }

    function urlBuilder($body, $storekey) {

        $apikey = env('API2CART_KEY');

        $baseUrl = "https://api.api2cart.com/v1.1/product.add.json?";

        $storekey = env('API2CART_STORE_KEY');
        $url = "$baseUrl"."api_key=$apikey"
                ."&store_key=$storekey"
                ."&name=$body[ProductName]"
                ."&model=$body[ProductModel]"
                ."&description=$body[ProductDescription]"
                ."&price=$body[Price]";
return "https://api.api2cart.com/v1.1/product.add.json?api_key=$apikey&store_key=$storekey&name=$body[ProductName]&model=$body[ProductModel]&description=$body[ProductDescription]&price=$body[Price]";
        // return "https://api.api2cart.com/v1.1/product.add.json?api_key=$apikey&store_key=$storekey&name=$body[ProductName]&model=$body[ProductModel]&description=$body[ProductDescription]&price=$body[Price]";

    }
}
