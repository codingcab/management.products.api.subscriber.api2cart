<?php


namespace App\Api2cart;


use GuzzleHttp\Client;

class Api2Cart_Base
{
    public $guzzle;

    private $exceptions = true;
    public $store_key = null;
    public $api_key = null;

    public function __construct(String $store_key, bool $exceptions = true)
    {
        $this->exceptions = $exceptions;
        $this->api_key = env('API2CART_API_KEY', '');
        $this->store_key = $store_key;

        $this->guzzle = new Client([
            'base_uri' =>  'https://api.api2cart.com/v1.1/',
            'timeout' => 60,
            'exceptions' => true,
        ]);
    }

}
