<?php

namespace Tests\Unit;

use App\Api2cart\Products;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    const API2CART_DEMO_STORE_KEY = "ed58a22dfecb405a50ea3ea56979360d";

    public function test_getProductInfo_method()
    {
        $product = Products::getProductInfo(self::API2CART_DEMO_STORE_KEY, "123456");

        $this->assertIsArray($product);
    }
}
