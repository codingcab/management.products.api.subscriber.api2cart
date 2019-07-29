<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('addProduct', 'SNSController@handleRequest');

Route::get('test', function () {


    $guzzleClient = new \GuzzleHttp\Client();

    $guzzleResponse = $guzzleClient->request('get', 'https://api.api2cart.com/v1.0/product.list.json?api_key=e3d14cb857bb7c271f26c1fbe2b00470&store_key=ed58a22dfecb405a50ea3ea56979360d');

    $testResponse = $guzzleResponse->getBody();

    return $testResponse;
    Log::info($testResponse);

});
