<?php

namespace Tests\Feature;

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

        Bus::assertDispatched(SyncProductJob::class);
    }
}
