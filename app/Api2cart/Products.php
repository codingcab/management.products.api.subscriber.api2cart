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
     * @return array|null
     * @throws Exception
     */
    static function find(string $store_key, string $sku)
    {
        $product = Products::findSimpleProduct($store_key, $sku);

        if($product) {
            return $product;
        }

        $variant = Products::findVariant($store_key, $sku);

        if($variant) {
            return $variant;
        }

        return null;
    }

    static function getProductInfo(string $store_key, string $sku)
    {
        $product_id = Products::getSimpleProductID($store_key, $sku);;

        if(empty($product_id)) {
            return null;
        }

        $response =  Client::GET($store_key,'product.info.json', [
            'id' => $product_id,
            'params' => "force_all"
        ]);

        if($response->isNotSuccess()) {
            return null;
        }

        $product = $response->content()['result'];

        $product["sku"]             = empty($product["u_sku"]) ? $product["u_model"] : $product["u_sku"];
        $product["model"]           = $product["u_model"];
        $product["special_price"]   = $product["special_price"]["value"];

        return $product;

    }

    /**
     * @param string $store_key
     * @param string $sku
     * @return int|null
     */
    static function getSimpleProductID(string $store_key, string $sku)
    {
        $response =  Client::GET($store_key,'product.find.json', [
            'find_where' => "model",
            'find_value' => $sku
        ]);

        if($response->isNotSuccess()) {
            return null;
        }

        return $response->content()['result']['product'][0]["id"];
    }

    /**
     * @param string $store_key
     * @param string $sku
     * @return array|null
     */
    static function findSimpleProduct(string $store_key, string $sku)
    {
        $response =  Client::GET($store_key,'product.find.json', [
                'find_where' => "model",
                'find_value' => $sku,
//                'store_id' => 0
            ]);

        if($response->isSuccess()) {
            return $response->content()['result']['product'][0];
        }

        return null;
    }

    /**
     * @param string $store_key
     * @param string $sku
     * @return array|null
     */
    static function findVariant(string $store_key, string $sku)
    {
        $response = Client::GET($store_key,'product.child_item.find.json', [
            'find_where' => 'sku',
            'find_value' => $sku,
//            'store_id' => 0
        ]);

        if($response->isSuccess()) {
            return $response->content()['result']['children'][0];
        }

        return null;
    }

    /**
     * @param string $store_key
     * @param int $product_id
     * @return RequestResponse
     */
    static function deleteProduct(string $store_key, int $product_id)
    {
        return Client::DELETE($store_key,'product.delete.json', ['id' => $product_id]);
    }

    /**
     * @param string $store_key
     * @param array $product_data
     * @return RequestResponse
     * @throws Exception
     */
    static function createSimpleProduct(string $store_key, array $product_data)
    {
        $product = Arr::only($product_data, self::PRODUCT_ALLOWED_KEYS);

        // disable new products
        $product["available_for_view"] = false;
        $product["available_for_sale"] = false;

        $response = Client::POST($store_key,'product.add.json', $product);

        if($response->isNotSuccess()) {
            Log::error('Product create failed', $response->content());
            throw new Exception('Product create failed', $response->returnCode());
        }

        return $response;
    }

    /**
     * @param string $store_key
     * @param array $product_data
     * @return RequestResponse
     * @throws Exception
     */
    static function updateSimpleProduct(string $store_key, array $product_data)
    {
        $product = Arr::only($product_data, self::PRODUCT_ALLOWED_KEYS);

        $product = Arr::except($product, self::PRODUCT_DONT_UPDATE_KEYS);

        $response = Client::POST($store_key, 'product.update.json', $product);

        if($response->isNotSuccess()) {
            Log::error('Product update failed', $response->content());
            throw new Exception('Product update failed', $response->returnCode());
        }

        return $response;
    }

    /**
     * This will only update variant product, will not update simple product
     * @param string $store_key
     * @param array $variant_data
     * @return RequestResponse
     * @throws Exception
     */
    static function updateVariant(string $store_key, array $variant_data)
    {
        $properties = Arr::only($variant_data, self::PRODUCT_ALLOWED_KEYS);

        $properties = Arr::except($properties, self::PRODUCT_DONT_UPDATE_KEYS);

        $response = Client::POST($store_key,'product.variant.update.json', $properties);

        if($response->isNotSuccess()) {
            Log::error('Variant update failed', $response->content());
            throw new Exception('Variant update failed', $response->returnCode());
        }

        return $response;

    }

    /**
     * @param string $store_key
     * @param array $product_data
     * @return RequestResponse
     * @throws Exception
     */
    static function updateOrCreate(string $store_key, array $product_data)
    {
        $product_id = Products::getSimpleProductID($store_key, $product_data['sku']);

        if(!empty($product_id)) {
            $properties = array_merge($product_data, ['id' => $product_id]);
            return Products::updateSimpleProduct($store_key, $properties);
        }

        $variant = Products::findVariant($store_key, $product_data['sku']);

        if(!empty($variant)) {
            $properties = array_merge($product_data, ['id' => $variant["id"]]);
            return Products::updateVariant($store_key, $properties);
        }

        return Products::createSimpleProduct($store_key, $product_data);
    }

}
