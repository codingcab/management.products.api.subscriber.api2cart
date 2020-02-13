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
     * @param string $store_key
     * @param string $sku
     * @return mixed|null
     * @throws Exception
     */
    static function find(string $store_key, string $sku)
    {
        $productClient = new static($store_key);

        $product = $productClient->findSimpleProduct($sku);

        if($product) {
            return $product;
        }

        $variant = $productClient->findVariant($sku);

        if($variant) {
            return $variant;
        }

        return null;
    }

    /**
     * @param string $sku
     * @return int|null
     * @throws Exception
     */
    public function findProductId(string $sku)
    {
        $product = $this->findSimpleProduct($sku);

        if(empty($product)) {
            return null;
        }

        return $product->id;
    }

    /**
     * @param string $sku
     * @return mixed|null
     * @throws Exception
     */
    public function findSimpleProduct(string $sku)
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
    public function createSimpleProduct(array $product_data)
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
    public function updateSimpleProduct(array $product_data)
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
     * @param array $product_data
     * @return RequestResponse
     * @throws Exception
     */
    public function updateOrCreate(array $product_data)
    {
        $product = $this->findSimpleProduct($product_data['sku']);

        if(!empty($product)) {
            $properties = array_merge($product_data, ['id' => $product->id]);
            return $this->updateSimpleProduct($properties);
        }

        $variant = $this->findVariant($product_data['sku']);

        if(!empty($variant)) {
            $properties = array_merge($product_data, ['id' => $variant->id]);
            return $this->updateVariant($properties);
        }

        return $this->createSimpleProduct($product_data);
    }

}
