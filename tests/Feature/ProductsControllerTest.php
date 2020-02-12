<?php

namespace Tests\Feature;

use App\Http\Controllers\ProductsController;
use App\Jobs\SyncProductJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Testing\Fakes\BusFake;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductsControllerTest extends TestCase
{
    public function test_if_SyncProductJob_is_dispatched()
    {
        Bus::fake();

        $products_controller = new ProductsController();

        $product_notification = [
            "sku" => "123456",
            "name" => "Test Product",
            "price" => 9.99,
            "sale_price" => 4.99,
            "sale_price_start_date" => '2019-01-01 00:00',
            "sale_price_end_date" => '2019-01-01 00:00',
            "quantity_available" => 10
        ];

        $products_controller->handleIncomingNotification($product_notification,'', 0);

        Bus::assertDispatched(SyncProductJob::class);
    }
}
