<?php

namespace App\Http\Controllers;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class Api2cartController
{
    private const PRODUCT_ALLOWED_KEYS = [
        "id",
        "model",
        "name",
        "description",
        "price",
        "special_price",
        "sprice_create",
        "sprice_expire",
        "quantity",
        "in_stock",
        "store_id"
    ];

    public $lastResponse;

    private $guzzle;

    private $exceptions = true;
    private $store_key = null;
    private $api_key = null;

    public static function connect(String $store_key)
    {
        return new Api2cartController($store_key);
    }

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

    public function listVariants()
    {
        $response =  $this->guzzle->get('product.variant.list.json', [
            'query' => [
                'api_key' => $this->api_key,
                'store_key' => $this->store_key,
            ]
        ]);

        $this->lastResponse = json_decode($response->getBody()->getContents(), false);

    }

    public function findProductId(String $sku)
    {
        $product = $this->findProduct($sku);

        if(empty($product)) {
            return null;
        }

        return $product->id;
    }

    public function findProduct(String $sku)
    {
        $response =  $this->guzzle->post('product.find.json', [
            'query' => [
                'api_key' => $this->api_key,
                'store_key' => $this->store_key,
                'find_value' => $sku,
                'find_where' => 'model'
            ]
        ]);

        $this->lastResponse = json_decode($response->getBody()->getContents(), false);

        if($this->lastResponse->return_code === 0) {
            return $this->lastResponse->result->product[0];
        }

        return null;
    }

    /**
     * @param int $product_id
     * @return mixed
     */
    public function deleteProduct(int $product_id)
    {
        return $this->delete('product.delete.json', ['id' => $product_id]);
    }

    public function createProduct(array $product_data)
    {
        $data = Arr::only($product_data, self::PRODUCT_ALLOWED_KEYS);

        return $this->post('product.add.json', $data);
    }

    /**
     * @param $store_key
     * @param $product_data
     * @return false|string
     * @throws Exception
     */
    public function updateProduct($product_data)
    {
        $data = Arr::only($product_data, self::PRODUCT_ALLOWED_KEYS);

        return $this->post('product.update.json', $data);
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     */
    private function post($uri, $data)
    {
        $response = $this->guzzle->post($uri, [
            'query' => [
                'api_key' => $this->api_key,
                'store_key' => $this->store_key,
            ],
            'json' => $data
        ]);

        $this->lastResponse = json_decode($response->getBody()->getContents(), false);

        return $this->lastResponse;
    }

    /**
     * @param int $product_id
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
