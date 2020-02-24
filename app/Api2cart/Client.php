<?php


namespace App\Api2cart;

use GuzzleHttp\Client as GuzzleClient;
use Mockery\Exception;

class Client
{
    /**
     * @param string $store_key
     * @param string $uri
     * @param array $params
     * @return RequestResponse
     */
    static function GET(string $store_key, string $uri, array $params)
    {
        $query = [
            'api_key' => self::getApiKey(),
            'store_key' => $store_key
        ];

        $query = array_merge($query, $params);

        $response = new RequestResponse(
            self::getGuzzleClient()->get($uri, ['query' => $query])
        );

        logger("GET", [
           "uri" => $uri,
           "query" => $query,
           "response" => [
                "status_code" => $response->getResponseRaw()->getStatusCode(),
                "body" => $response->asArray()
           ]
        ]);

        return $response;
    }

    /**
     * @param string $store_key
     * @param string $uri
     * @param array $data
     * @return RequestResponse
     */
    static function POST(string $store_key, string $uri, array $data)
    {
        $query = [
            'api_key' => self::getApiKey(),
            'store_key' => $store_key
        ];

        $response = self::getGuzzleClient()->post($uri, [
            'query' => $query,
            'json' => $data
        ]);

        return new RequestResponse($response);
    }

    /**
     * @param string $store_key
     * @param string $uri
     * @param array $params
     * @return RequestResponse
     */
    static function DELETE(string $store_key, string $uri, array $params)
    {
        $query = [
            'api_key' => self::getApiKey(),
            'store_key' => $store_key
        ];

        $query = array_merge($query, $params);

        $response =  self::getGuzzleClient()->delete($uri, ['query' => $query]);

        return new RequestResponse($response);
    }

    static function getGuzzleClient()
    {
        return new GuzzleClient([
            'base_uri' =>  'https://api.api2cart.com/v1.1/',
            'timeout' => 60,
            'exceptions' => true,
        ]);
    }

    static function getApiKey()
    {
        return env('API2CART_API_KEY', '');
    }

}
