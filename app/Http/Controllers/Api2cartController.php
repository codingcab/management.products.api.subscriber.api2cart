<?php

namespace App\Http\Controllers;

use App\Api2cart\Api2Cart_Base;
use App\Api2cart\Api2CartResponse;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class Api2cartController extends Api2Cart_Base
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

    private const PRODUCT_DONT_UPDATE_KEYS = [
        "name",
        "description"
    ];



    public static function connect(String $store_key)
    {
        return new Api2cartController($store_key);
    }

    /**
     * @param String $sku
     * @return int|null
     */
    public function findProductId(String $sku)
    {
        $product = $this->findProduct($sku);

        if(empty($product)) {
            return null;
        }

        return $product->id;
    }

    /**
     * @param String $sku
     * @return mixed|null
     */
    public function findProduct(String $sku)
    {
        $response =  $this->get('product.find.json', [
                'find_value' => $sku,
                'find_where' => 'model'
            ]);

        if($response->isSuccess()) {
            return $response->jsonContent()->result->product[0];
        }

        return null;
    }

    /**
     * @param int $product_id
     * @return Api2CartResponse
     */
    public function deleteProduct(int $product_id)
    {
        return $this->delete('product.delete.json', ['id' => $product_id]);
    }

    /**
     * @param array $product_data
     * @return Api2CartResponse
     */
    public function createProduct(array $product_data)
    {
        $data = Arr::only($product_data, self::PRODUCT_ALLOWED_KEYS);

        return $this->post('product.add.json', $data);
    }

    /**
     * @param $product_data
     * @return false|string
     */
    public function updateProduct($product_data)
    {
        $data_create = Arr::only($product_data, self::PRODUCT_ALLOWED_KEYS);

        $data_update = Arr::except($data_create, self::PRODUCT_DONT_UPDATE_KEYS);

        $response = $this->post('product.update.json', $data_update);

        if($response->returnCode() == self::RETURN_CODE_OK) {
            return $response;
        }

        $response = $this->post('product.variant.update.json', $data_update);

        if($response->returnCode() == self::RETURN_CODE_OK) {
            return $response;
        }

        $response = $this->post('product.add.json', $data_create);

        if($response->returnCode() == self::RETURN_CODE_OK) {
            return $response;
        }

        return $response;
    }

}
