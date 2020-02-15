<?php

namespace Tests\Feature;

use App\Jobs\SyncProductJob;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SyncProductJobTest extends TestCase
{
    const API2CART_DEMO_STORE_KEY = "ed58a22dfecb405a50ea3ea56979360d";

    const SAMPLE_PRODUCT = [
        "sku" => "123456",
        "model" => "123456",
        "name" => "Sample product name",
        "description" => "Sample product description",
        "price" => 1
    ];

    public function test_if_job_runs()
    {
        $job = new SyncProductJob(self::API2CART_DEMO_STORE_KEY, self::SAMPLE_PRODUCT);

        $job->handle();

        // we  happy to see no exceptions :)
        // no more needed
        $this->assertTrue(True);
    }
}
