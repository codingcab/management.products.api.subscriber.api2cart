<?php

namespace App\Api2cart;

use Illuminate\Support\Arr;

class Api2Cart_Product extends Api2Cart_Base
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
     * @param string $sku
     * @return mixed|null
     */
    public function findVariant(string $sku)
    {
        $response = $this->get('product.child_item.find', [
            'find_where' => 'sku',
            'find_value' => $sku
        ]);

        if($response->isSuccess()) {
            return $response->jsonContent()->result->children[0];
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
     * This will only update simple product, will not update variant
     * @param $product_data
     * @return Api2CartResponse
     */
    public function updateProduct($product_data)
    {
        $data_create = Arr::only($product_data, self::PRODUCT_ALLOWED_KEYS);

        $data_update = Arr::except($data_create, self::PRODUCT_DONT_UPDATE_KEYS);

        return $this->post('product.update.json', $data_update);
    }

    /**
     * This will only update variant product, will not update simple product
     * @param $data
     * @return Api2CartResponse
     */
    public function updateVariant($data)
    {
        $properties = Arr::only($data, self::PRODUCT_ALLOWED_KEYS);

        $properties = Arr::except($properties, self::PRODUCT_DONT_UPDATE_KEYS);

        return $this->post('product.variant.update.json', $properties);
    }

}
