<?php

namespace Tests\Feature;

use App\Jobs\SyncProductJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
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

    public function test_successful_request()
    {
        Bus::fake();

        $api2cart_store_key = 'abc123';

        $data = [
            "sku" => "123456",
            "name" => "Test Product",
            "price" => 9.99,
            "sale_price" => 4.99,
            "sale_price_start_date" => '2019-01-01 00:00',
            "sale_price_end_date" => '2019-01-01 00:00',
            "quantity_available" => 10
        ];

        $response = $this->json('POST',"/api/api2cart/$api2cart_store_key/products/store/0", $data);

        $response->assertStatus(200);

        Bus::assertDispatched(SyncProductJob::class);
    }
}
