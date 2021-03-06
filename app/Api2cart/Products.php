<?php

namespace App\Api2cart;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Api2cart\RequestResponse;

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
     * @param int|null $store_id
     * @return array|null
     */
    static function getSimpleProductInfo(string $store_key, string $sku, int $store_id = null)
    {
        $product_id = Products::getSimpleProductID($store_key, $sku);

        if(empty($product_id)) {
            return null;
        }

        $params = [
            "id" => $product_id,
            "params" => implode(",",[
                "id",
                "model",
                "u_model",
                "sku",
                "u_sku",
                "price",
                "special_price",
                "stores_ids",
                "quantity"
            ]),
        ];

        if($store_id) {
            $params["store_id"] = $store_id;
        }

        $response =  Client::GET($store_key,'product.info.json', $params);

        if($response->isNotSuccess()) {
            return null;
        }

        $product = $response->getResult();


        $product["type"]            = "product";
        $product["sku"]             = empty($product["u_sku"]) ? $product["u_model"] : $product["u_sku"];
        $product["model"]           = $product["u_model"];

        $product["sprice_create"]   = empty($product["special_price"]["created_at"]) ? "1900-01-01 00:00:00" : $product["special_price"]["created_at"]["value"];
        $product["sprice_create"] = Carbon::createFromTimeString( $product["sprice_create"])->format ("Y-m-d H:i:s");

        $product["sprice_expire"]   = empty($product["special_price"]["expired_at"]) ? "1900-01-01 00:00:00" : $product["special_price"]["expired_at"]["value"];
        $product["sprice_expire"] = Carbon::createFromTimeString( $product["sprice_expire"])->format ("Y-m-d H:i:s");

        $product["special_price"]   = $product["special_price"]["value"];

        return $product;
    }

    /**
     * @param string $store_key
     * @param string $sku
     * @param int|null $store_id
     * @return array|null
     */
    static function getVariantInfo(string $store_key, string $sku, int $store_id = null)
    {
        $variant_id = Products::getVariantID($store_key, $sku);

        if(empty($variant_id)) {
            return null;
        }

        $params = [
            "id" => $variant_id,
            "params" => "force_all",
        ];

        if($store_id) {
            $params["store_id"] = $store_id;
        }

        $response =  Client::GET($store_key,'product.variant.info.json', $params);

        if($response->isNotSuccess()) {
            return null;
        }

        $variant = $response->getResult()["variant"];

        $variant['type']            = "variant";
        $variant["sku"]             = empty($variant["u_sku"]) ? $variant["u_model"] : $variant["u_sku"];
        $variant["model"]           = $variant["u_model"];
        $variant["sprice_create"]   = empty($variant["special_price"]["created_at"]) ? "1900-01-01 00:00:00":$variant["special_price"]["created_at"]["value"];
        $variant["sprice_create"] = Carbon::createFromTimeString( $variant["sprice_create"])->format ("Y-m-d H:i:s");

        $variant["sprice_expire"]   = empty($variant["special_price"]["expired_at"]) ? "1900-01-01 00:00:00":$variant["special_price"]["expired_at"]["value"];
        $variant["sprice_expire"] = Carbon::createFromTimeString( $variant["sprice_expire"])->format ("Y-m-d H:i:s");

        $variant["special_price"]   = $variant["special_price"]["value"];

        return $variant;
    }

    /**
     * @param string $store_key
     * @param string $sku
     * @param int|null $store_id
     * @return array|null
     */
    static function getProductInfo(string $store_key, string $sku, int $store_id = null)
    {
        $product = Products::getSimpleProductInfo($store_key, $sku, $store_id);

        if($product) {
            return $product;
        }

        $variant = Products::getVariantInfo($store_key, $sku, $store_id);

        if($variant) {
            return $variant;
        }

        return null;
    }

    /**
     * @param string $store_key
     * @param string $sku
     * @return int|null
     */
    static function getSimpleProductID(string $store_key, string $sku)
    {
        $cache_key = $store_key."_".$sku."_product_id";

        $id = Cache::get($cache_key);

        if($id) {
            return $id;
        }

        $response =  Client::GET($store_key,'product.find.json', [
            'find_where' => "model",
            'find_value' => $sku,
            'store_id' => 0
        ]);

        if($response->isNotSuccess()) {
            return null;
        }

        $id = $response->getResult()['product'][0]["id"];

        Cache::put($cache_key, $id, 60 * 24 * 7);

        return $id;
    }

    /**
     * @param string $store_key
     * @param string $sku
     * @return int|null
     */
    static function getVariantID(string $store_key, string $sku)
    {
        $cache_key = $store_key."_".$sku."_variant_id";

        $id = Cache::get($cache_key);

        if($id) {
            return $id;
        }

        $response =  Client::GET($store_key,'product.child_item.find.json', [
            'find_where' => "sku",
            'find_value' => $sku
        ]);

        if($response->isNotSuccess()) {
            return null;
        }

        $id = $response->getResult()['children'][0]["id"];

        Cache::put($cache_key, $id, 60 * 24 * 7);

        return $id;
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
        $fields = [
            "sku",
            "model",
            "name",
            "description",
            "price"
        ];

        $product = Arr::only($product_data, $fields);

        if(!Arr::has($product_data, "model"))
        {
            $product["model"] = $product_data["sku"];
        }

        // disable new products
        $product["available_for_view"] = false;
        $product["available_for_sale"] = false;

        $response = Client::POST($store_key,'product.add.json', $product);

        if($response->isSuccess()) {
            Log::info('Product created', $product_data);
            return $response;
        }

        Log::error("product.add.json failed", $response->asArray());
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

        $response = Client::GET($store_key, 'product.update.json', $product);

        if($response->isSuccess()) {
            info("Product updated", $product);
            return $response;
        }

        switch ($response->getReturnCode()) {
            case RequestResponse::RETURN_CODE_MODEL_NOT_FOUND:
                Products::assignStore($store_key, $product_data['id'], $product_data['store_id']);
                return self::updateSimpleProduct($store_key, $product_data);
                break;
        }

        Log::error('product.update.json failed', $response->asArray());
        return $response;
    }

    /**
     * @param string $store_key
     * @param int $product_id
     * @param int $store_id
     * @return RequestResponse
     * @throws Exception
     */
    static function assignStore(string $store_key, int $product_id, int $store_id)
    {
        $data = [
            "product_id" => $product_id,
            "store_id" => $store_id
        ];

        $response = Client::POST($store_key, 'product.store.assign.json', $data);

        if($response->isSuccess()) {
            Log::info('Store assigned', $data);
            return $response;
        }

        Log::error('product.store.assign.json failed', [
            "product_id" => $product_id,
            "store_id" => $store_id,
            "response" => $response->asArray()
        ]);

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

        $response = Client::GET($store_key,'product.variant.update.json', $properties);

        if($response->isSuccess()) {
            info("Variant updated", $properties);
            return $response;
        }

        Log::error('product.variant.update.json failed', $response->asArray());
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
        $product = Products::getProductTypeAndId($store_key, $product_data['sku']);

        switch ($product["type"]) {
            case "product":
                $properties = array_merge($product_data, ['id' => $product["id"]]);
                return Products::updateSimpleProduct($store_key, $properties);
                break;
            case "variant":
                $properties = array_merge($product_data, ['id' => $product["id"]]);
                return Products::updateVariant($store_key, $properties);
                break;
            default:
                return Products::createSimpleProduct($store_key, $product_data);
                break;
        }


    }

    /**
     * @param string $store_key
     * @param string $sku
     * @return array
     */
    private static function getProductTypeAndId(string $store_key, string $sku)
    {
        $cache_key = $store_key."_".$sku."_getProductTypeAndId";

        $cached_product = Cache::get($cache_key);

        if($cached_product) {
            return $cached_product;
        }

        $product_id = Products::getSimpleProductID($store_key, $sku);

        if (!empty($product_id)) {

            $product = [
                "type" => "product",
                "id" => $product_id
            ];

            Cache::put($cache_key, $product, 60 * 24 * 7);

            return $product;
        }

        $variant_id = Products::getVariantID($store_key, $sku);

        if (!empty($variant_id)) {

            $product = [
                "type" => "variant",
                "id" => $variant_id
            ];

            Cache::put($cache_key, $product, 60 * 24 * 7);

            return $product;

        }

        return [
            "type" => null,
            "id" => null
        ];

    }

}
