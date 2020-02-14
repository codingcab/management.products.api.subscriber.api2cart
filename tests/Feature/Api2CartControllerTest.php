<?php

namespace Tests\Feature;

use App\Api2cart\Products;
use Exception;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Api2CartControllerTest extends TestCase
{
    const API2CART_DEMO_STORE_KEY = "ed58a22dfecb405a50ea3ea56979360d";

    const SAMPLE_PRODUCT = [
        "model" => "123456",
        "name" => "Sample product name",
        "description" => "Sample product description",
        "price" => 1
    ];

    /**
     *
     */
    public function test_successfull_product_delete()
    {
        $product_id = Products::getSimpleProductID(self::API2CART_DEMO_STORE_KEY, self::SAMPLE_PRODUCT['model']);

        if(empty($product_id)) {
            $response = Products::createSimpleProduct(self::API2CART_DEMO_STORE_KEY, self::SAMPLE_PRODUCT);

            $this->assertEquals(0, $response->getReturnCode());

            $product_id = $response->content()["result"]["product_id"];
        }

        $result = Products::deleteProduct(self::API2CART_DEMO_STORE_KEY, $product_id);

        $this->assertEquals(0, $result->getReturnCode());
    }

    /**
     *
     */
    public function test_successfull_product_create()
    {
        $product_id = Products::getSimpleProductID(self::API2CART_DEMO_STORE_KEY, self::SAMPLE_PRODUCT['model']);

        if (!empty($product_id)) {
            $response = Products::deleteProduct(self::API2CART_DEMO_STORE_KEY, $product_id);

            $this->assertEquals(0, $response->getReturnCode());
        }

        $product = Products::createSimpleProduct(self::API2CART_DEMO_STORE_KEY, self::SAMPLE_PRODUCT);

        $this->assertEquals(0, $product->getReturnCode());
    }

    /**
     * @throws Exception
     */
    public function test_successful_product_update()
    {
        $product_id = Products::getSimpleProductID(self::API2CART_DEMO_STORE_KEY, self::SAMPLE_PRODUCT['model']);

        if(empty($product_id)) {
            $response = Products::createSimpleProduct(self::API2CART_DEMO_STORE_KEY, self::SAMPLE_PRODUCT);

            $this->assertEquals(0, $response->getReturnCode());
        }


        $product_before = Products::getProductInfo(self::API2CART_DEMO_STORE_KEY, self::SAMPLE_PRODUCT['model']);

        $update_params = [
            "id" => $product_before["id"],
            "price" => $product_before["price"] + 1,
        ];

        Products::updateSimpleProduct(self::API2CART_DEMO_STORE_KEY, $update_params);

        $product_after = Products::getProductInfo(self::API2CART_DEMO_STORE_KEY, self::SAMPLE_PRODUCT['model']);

        $this->assertEquals($product_before["price"] + 1, $product_after["price"]);

    }
}
