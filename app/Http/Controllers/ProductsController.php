<?php

namespace App\Http\Controllers;

class ProductsController extends SNSController
{

    public function handleNotification($notification, $storekey){

        $body = $notification['Body'];

        $guzzlePostClient = new \GuzzleHttp\Client();

        $url = $this->urlBuilder($body, $storekey);

        $res = $guzzlePostClient->post($url);

        $responseJSON = json_decode($res->getBody(), true);

        if($responseJSON["return_code"] == 0) {

            return "Item added! Product ID: ".$responseJSON["result"]["product_id"];

        }

        return $responseJSON["return_message"];


    }

    public function urlBuilder($body, $storekey) {

        $apikey = env('API2CART_KEY');

        $baseUrl = "https://api.api2cart.com/v1.1/product.add.json?";

        $storekey = $storekey;

        $url = "$baseUrl"."api_key=$apikey"
                ."&store_key=$storekey"
                ."&name=$body[ProductName]"
                ."&model=$body[ProductModel]"
                ."&description=$body[ProductDescription]"
                ."&price=$body[Price]";

        return $url;
    }

}
