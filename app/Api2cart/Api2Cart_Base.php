<?php


namespace App\Api2cart;

use GuzzleHttp\Client;

class Api2Cart_Base
{
    public $guzzle;

    private $exceptions = true;
    private $store_key = null;
    private $api_key = null;

    /**
     * Api2Cart_Base constructor.
     * @param string $store_key
     * @param bool $exceptions
     */
    public function __construct(string $store_key, bool $exceptions = true)
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

    /**
     * @param string $uri
     * @param array $params
     * @return Api2CartResponse
     */
    public function get(string $uri, array $params)
    {
        $query = [
            'api_key' => $this->api_key,
            'store_key' => $this->store_key
        ];

        $query = array_merge($query, $params);

        $response = $this->guzzle->get($uri, ['query' => $query]);

        return new Api2CartResponse($response);
    }

    /**
     * @param string $uri
     * @param array $data
     * @return Api2CartResponse
     */
    public function post(string $uri, array $data)
    {
        $query = [
            'api_key' => $this->api_key,
            'store_key' => $this->store_key
        ];

        $response = $this->guzzle->post($uri, [
            'query' => $query,
            'json' => $data
        ]);

        return new Api2CartResponse($response);
    }

    /**
     * @param string $uri
     * @param array $params
     * @return Api2CartResponse
     */
    public function delete(string $uri, array $params)
    {
        $query = [
            'api_key' => $this->api_key,
            'store_key' => $this->store_key
        ];

        $query = array_merge($query, $params);

        $response =  $this->guzzle->delete($uri, ['query' => $query]);

        return new Api2CartResponse($response);
    }

}
