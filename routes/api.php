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

Route::post('store/{store_key}/products', 'ProductsController@store');
Route::post('store/{store_key}/products/view/{store_id}', 'ProductsController@store');

Route::post('api2cart/{store_key}/products/store/{store_id}', 'ProductsController@store');

Route::get('store/{store_key}/orders', 'OrdersController@index');

