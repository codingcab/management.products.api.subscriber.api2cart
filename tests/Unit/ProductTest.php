<?php

namespace Tests\Unit;

use App\Api2cart\Products;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    const API2CART_DEMO_STORE_KEY = "ed58a22dfecb405a50ea3ea56979360d";

    public function test_Product_find_static_method()
    {
        $product = Products::find(self::API2CART_DEMO_STORE_KEY, "123456");

        // test should pass if no exceptions occurred
        // regardless of the result
        $this->assertTrue(true);
    }

    public function test_if_findSimpleProduct_returns_array()
    {
        $manager = new Products(self::API2CART_DEMO_STORE_KEY);

        $product = $manager->findSimpleProduct("123456");

        $this->assertIsArray($product);
    }

    public function test_getProductInfo_method()
    {
        $product = Products::getProductInfo(self::API2CART_DEMO_STORE_KEY, "123456");

        $this->assertIsArray($product);
    }
}
