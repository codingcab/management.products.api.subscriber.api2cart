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
        $api2cart = new Products(self::API2CART_DEMO_STORE_KEY, false);

        $product_id = $api2cart->findProductId(self::SAMPLE_PRODUCT['model']);

        if(empty($product_id)) {
            $response = $api2cart->createProduct(self::SAMPLE_PRODUCT);

            $this->assertEquals(0, $response->returnCode());

            $product_id = $response->jsonContent()->result->product_id;
        }

        $result = $api2cart->deleteProduct($product_id);

        $this->assertEquals(0, $result->returnCode());
    }

    /**
     *
     */
    public function test_successfull_product_create()
    {
        $api2cart = new Products(self::API2CART_DEMO_STORE_KEY, false);

        $product_id = $api2cart->findProductId(self::SAMPLE_PRODUCT['model']);

        if (!empty($product_id)) {
            $response = $api2cart->deleteProduct($product_id);

            $this->assertEquals(0, $response->returnCode());
        }

        $product = $api2cart->createProduct(self::SAMPLE_PRODUCT);

        $this->assertEquals(0, $product->returnCode());
    }

    /**
     * @throws Exception
     */
    public function test_successful_product_update()
    {
        $api2cart = new Products(self::API2CART_DEMO_STORE_KEY, false);

        $product_id = $api2cart->findProductId(self::SAMPLE_PRODUCT['model']);

        if(empty($product_id)) {
            $response = $api2cart->createProduct(self::SAMPLE_PRODUCT);

            $this->assertEquals(0, $response->returnCode());

            $product_id = $response->jsonContent()->result->product_id;
        }


        $product_before = $api2cart->findSimpleProduct(self::SAMPLE_PRODUCT['model']);

        $update_params = [
            "id" => $product_before->id,
            "price" => $product_before->price + 1,
        ];

        $api2cart->updateSimpleProduct($update_params);

        $product_after = $api2cart->findSimpleProduct(self::SAMPLE_PRODUCT['model']);

        $this->assertEquals($product_before->price + 1, $product_after->price);

    }
}
