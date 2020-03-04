<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductsRouteTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_if_empty_post_is_not_allowed()
    {
        $api2cart_store_key = 'abc123';

        $response = $this->json('POST',"/api/api2cart/$api2cart_store_key/products/store/0",[]);

        $response->assertStatus(422);
    }

    public function test_if_sku_is_required()
    {
        $api2cart_store_key = 'abc123';

        $data = [
            "skk" => "123" //consciously using wrong working to force ProductPostRequest validation to fail
        ];

        $response = $this->json('POST',"/api/api2cart/$api2cart_store_key/products/store/0", $data);

        $response->assertStatus(422);
    }
}
