<?php

namespace App\Api2cart;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class Products extends Entity
{
    private const PRODUCT_ALLOWED_KEYS = [
        "id",
        "sku",
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
        "model",
        "sku",
        "name",
        "description"
    ];

    /**
     * @param String $sku
     * @return int|null
     * @throws Exception
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
     * @throws Exception
     */
    public function findProduct(String $sku)
    {
        if(empty($sku)) {
            throw new Exception('SKU not specified');
        }

        $response =  $this->client()->get('product.find.json', [
                'find_value' => $sku,
                'find_where' => 'model',
                'store_id' => 0
            ]);

        if($response->isSuccess()) {
            return $response->jsonContent()->result->product[0];
        }

        return null;
    }

    /**
     * @param string $sku
     * @return mixed|null
     * @throws Exception
     */
    public function findVariant(string $sku)
    {
        if(empty($sku)) {
            throw new Exception('SKU not specified');
        }

        $response = $this->client()->get('product.child_item.find.json', [
            'find_where' => 'sku',
            'find_value' => $sku,
//            'store_id' => 0
        ]);

        if($response->isSuccess()) {
            return $response->jsonContent()->result->children[0];
        }

        return null;
    }

    /**
     * @param int $product_id
     * @return RequestResponse
     * @throws Exception
     */
    public function deleteProduct(int $product_id)
    {
        if(empty($product_id)) {
            throw new Exception('Product_id not specified');
        }

        return $this->client()->delete('product.delete.json', ['id' => $product_id]);
    }

    /**
     * @param array $product_data
     * @return RequestResponse
     * @throws Exception
     */
    public function createProduct(array $product_data)
    {
        $product = Arr::only($product_data, self::PRODUCT_ALLOWED_KEYS);

        // disable new products
        $product["available_for_view"] = false;
        $product["available_for_sale"] = false;

        $response = $this->client()->post('product.add.json', $product);

        if($response->isNotSuccess()) {
            Log::error('Product create failed', $response->content());
            throw new Exception('Product create failed', $response->returnCode());
        }

        return $response;
    }

    /**
     * @param array $product_data
     * @return RequestResponse
     * @throws Exception
     */
    public function updateProduct(array $product_data)
    {
        $product = Arr::only($product_data, self::PRODUCT_ALLOWED_KEYS);

        $product = Arr::except($product, self::PRODUCT_DONT_UPDATE_KEYS);

        $response = $this->client()->post('product.update.json', $product);

        if($response->isNotSuccess()) {
            Log::error('Product update failed', $response->content());
            throw new Exception('Product update failed', $response->returnCode());
        }

        return $response;
    }

    /**
     * This will only update variant product, will not update simple product
     * @param array $variant_data
     * @return RequestResponse
     * @throws Exception
     */
    public function updateVariant(array $variant_data)
    {
        $properties = Arr::only($variant_data, self::PRODUCT_ALLOWED_KEYS);

        $properties = Arr::except($properties, self::PRODUCT_DONT_UPDATE_KEYS);

        $response = $this->client()->post('product.variant.update.json', $properties);

        if($response->isNotSuccess()) {
            Log::error('Variant update failed', $response->content());
            throw new Exception('Variant update failed', $response->returnCode());
        }

        return $response;

    }

    /**
     * @param array $data
     * @return RequestResponse
     * @throws Exception
     */
    public function updateOrCreate(array $data)
    {
        $product = $this->findProduct($data['sku']);

        if(!empty($product)) {
            $product_data = array_merge($data, ['id' => $product->id]);
            return $this->updateProduct($product_data);
        }

        $variant = $this->findVariant($data['sku']);

        if(!empty($variant)) {
            $variant_data = array_merge($data, ['id' => $variant->id]);
            return $this->updateVariant($variant_data);
        }

        return $this->createProduct($data);
    }

}
