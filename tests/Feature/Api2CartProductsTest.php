<?php

namespace Tests\Feature;

use App\Api2cart\Products;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Api2CartProductsTest extends TestCase
{
    const API2CART_DEMO_STORE_KEY = "ed58a22dfecb405a50ea3ea56979360d";

    const SAMPLE_PRODUCT = [
        "sku" => "123456",
        "name" => "Sample product name",
        "description" => "Sample product description",
        "price" => 10,
        "special_price" => 5,
        "sprice_create" => "2020-01-01 00:00:00",
        "sprice_expire" => "2020-01-01 00:00:00"
    ];

    public function test_if_getProductInfo_returns_required_fields()
    {
        $store_key = self::API2CART_DEMO_STORE_KEY;

        Products::updateOrCreate($store_key, self::SAMPLE_PRODUCT);

        $product = Products::getProductInfo($store_key, self::SAMPLE_PRODUCT["sku"]);

        $this->assertNotEmpty($product);

        $this->assertArrayHasKey("type", $product);
        $this->assertArrayHasKey("sku", $product);
        $this->assertArrayHasKey("model", $product);
        $this->assertArrayHasKey("price", $product);
        $this->assertArrayHasKey("special_price", $product);
        $this->assertArrayHasKey("sprice_create", $product);
        $this->assertArrayHasKey("sprice_expire", $product);
        $this->assertArrayHasKey("quantity", $product);
    }
}
