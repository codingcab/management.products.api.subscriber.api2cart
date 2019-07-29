<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

class AddProductController extends SNSController
{
    function handleNotification($notification)
    {
        Log::info('Listing all prodcuts for testing');

        $guzzleClient = new \GuzzleHttp\Client();

        $guzzleResponse = $guzzleClient->request('get', 'https://api.api2cart.com/v1.0/product.list.json?api_key=e3d14cb857bb7c271f26c1fbe2b00470&store_key=ed58a22dfecb405a50ea3ea56979360d');

        $testResponse = $guzzleResponse->getBody();

        return $testResponse;
        Log::info($testResponse);
    }
}
