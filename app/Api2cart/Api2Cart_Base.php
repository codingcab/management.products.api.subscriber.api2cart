<?php


namespace App\Api2cart;


use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class Api2Cart_Base
{
    public $guzzle;

    private $exceptions = true;
    public $store_key = null;
    public $api_key = null;

    public $lastResponse;

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

    /**
     * @param string $uri
     * @param array $params
     * @return ResponseInterface
     */
    public function get(string $uri, array $params)
    {
        return $this->guzzle->get($uri, ['query' => $params]);
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     */
    public function post($uri, $data)
    {
        $response = $this->guzzle->post($uri, [
            'query' => [
                'api_key' => $this->api_key,
                'store_key' => $this->store_key,
            ],
            'json' => $data
        ]);

        $this->lastResponse = json_decode($response->getBody()->getContents(), true);

        return $this->lastResponse;
    }

    /**
     * @param $uri
     * @param $params
     * @return mixed
     */
    public function delete($uri, $params)
    {
        $query = [
            'api_key' => $this->api_key,
            'store_key' => $this->store_key
        ];

        $query = array_merge($query, $params);

        $response =  $this->guzzle->delete($uri, ['query' => $query]);

        $this->lastResponse = json_decode($response->getBody()->getContents(), false);

        return $this->lastResponse;
    }

}
