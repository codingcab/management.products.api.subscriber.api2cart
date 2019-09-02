<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductsControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_if_returns_status_200_for_basic_data() {

        $response = $this->post('/api/store/123456789/products', [
            'sku' => '12345',
            'price' => 5
        ]);

        $response->assertStatus(200);
    }
}
