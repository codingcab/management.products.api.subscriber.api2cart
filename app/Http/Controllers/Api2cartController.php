<?php

namespace App\Http\Controllers;

use App\Api2cart\Api2Cart_Base;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class Api2cartController extends Api2Cart_Base
{
    const RETURN_CODE_OK = 0;
    const RETURN_CODE_MODEL_NOT_FOUND = 112;

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

    private const PRODUCT_DONT_UPDATE_KEYS = [
        "name",
        "description"
    ];



    public static function connect(String $store_key)
    {
        return new Api2cartController($store_key);
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
        $response =  $this->get('product.find.json', [
                'find_value' => $sku,
                'find_where' => 'model'
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
        $data_create = Arr::only($product_data, self::PRODUCT_ALLOWED_KEYS);

        $data_update = Arr::except($data_create, self::PRODUCT_DONT_UPDATE_KEYS);

        $response = $this->post('product.update.json', $data_update);

        if($response["return_code"] == self::RETURN_CODE_OK) {
            return $response;
        }

        $response = $this->post('product.variant.update.json', $data_update);

        if($response["return_code"] == self::RETURN_CODE_OK) {
            return $response;
        }

        $response = $this->post('product.add.json', $data_create);

        if($response["return_code"] == self::RETURN_CODE_OK) {
            return $response;
        }

        return $response;
    }

}
