<?php

namespace Tests\Unit;

use App\Api2cart\Products;
use App\Jobs\VerifyProductSyncJob;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerifyProductSyncJobTest extends TestCase
{
    const API2CART_DEMO_STORE_KEY = "ed58a22dfecb405a50ea3ea56979360d";

    public function test_when_all_matching()
    {
        $product_data = Products::getProductInfo(self::API2CART_DEMO_STORE_KEY, "123456");

        $job = new VerifyProductSyncJob(self::API2CART_DEMO_STORE_KEY, $product_data);

        $job->handle();

        $this->assertTrue(empty($job->getResults()["differences"]));
    }

    public function test_when_price_not_matching()
    {
        $product_data = Products::getProductInfo(self::API2CART_DEMO_STORE_KEY, "123456");

        $product_data["price"] = $product_data["price"] + 1;

        $job = new VerifyProductSyncJob(self::API2CART_DEMO_STORE_KEY, $product_data);

        $job->handle();

        $this->assertFalse(empty($job->getResults()["differences"]));
    }

    public function test_when_special_price_not_matching()
    {
        $product_data = Products::getProductInfo(self::API2CART_DEMO_STORE_KEY, "123456");

        $product_data["special_price"] = $product_data["special_price"] + 1;

        $job = new VerifyProductSyncJob(self::API2CART_DEMO_STORE_KEY, $product_data);

        $job->handle();

        $this->assertFalse(empty($job->getResults()["differences"]));
    }

    public function test_when_quantity_not_matching()
    {
        $product_data = Products::getProductInfo(self::API2CART_DEMO_STORE_KEY, "123456");

        $product_data["quantity"] = $product_data["quantity"] + 1;

        $job = new VerifyProductSyncJob(self::API2CART_DEMO_STORE_KEY, $product_data);

        $job->handle();

        $this->assertFalse(empty($job->getResults()["differences"]));
    }

    public function test_when_multiple_not_matching()
    {
        $product_data = Products::getProductInfo(self::API2CART_DEMO_STORE_KEY, "123456");

        $product_data["price"]          = $product_data["price"] + 1;
        $product_data["special_price"]  = $product_data["special_price"] + 1;
        $product_data["quantity"]       = $product_data["quantity"] + 1;

        $job = new VerifyProductSyncJob(self::API2CART_DEMO_STORE_KEY, $product_data);

        $job->handle();

        $this->assertFalse(empty($job->getResults()["differences"]));
    }
}
