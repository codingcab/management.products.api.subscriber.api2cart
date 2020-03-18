<?php

namespace Tests\Feature;

use App\Jobs\SyncProductJob;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SyncProductJobTest extends TestCase
{
    const API2CART_DEMO_STORE_KEY = "ed58a22dfecb405a50ea3ea56979360d";

    const SAMPLE_PRODUCT = [
        "sku" => "123456",
        "name" => "Sample product name",
        "price" => 2,
        "sale_price" => 1,
        "sale_price_start_date" => "2020-01-01 23:00:00",
        "sale_price_end_date" => "2030-01-01 15:01:02",
        "quantity_available" => 10,
        "store_id" => 0
    ];

    public function test_if_job_runs()
    {
        $job = new SyncProductJob(self::API2CART_DEMO_STORE_KEY, self::SAMPLE_PRODUCT);

        $job->handle();

        // we  happy to see no exceptions :)
        // no more needed
        $this->assertTrue(True);
    }

    public function test_if_convert_does_not_leave_timing_in_sale_start_date()
    {
        $job = new SyncProductJob(self::API2CART_DEMO_STORE_KEY, self::SAMPLE_PRODUCT);

        $date_actual = Carbon::createFromTimeString($job->_product_data["sprice_create"]);

        $date_string_without_time  = Carbon::createFromTimeString(self::SAMPLE_PRODUCT["sale_price_start_date"])->toDateString();

        $date_expected = Carbon::createFromTimeString($date_string_without_time . '00:00:00');

        $this->assertEquals($date_expected, $date_actual);
    }

    public function test_if_convert_does_not_leave_timing_in_sale_end_date()
    {
        $job = new SyncProductJob(self::API2CART_DEMO_STORE_KEY, self::SAMPLE_PRODUCT);

        $date_actual = Carbon::createFromTimeString($job->_product_data["sprice_expire"]);

        $date_string_without_time  = Carbon::createFromTimeString(self::SAMPLE_PRODUCT["sale_price_end_date"])->toDateString();

        $date_expected = Carbon::createFromTimeString($date_string_without_time . '00:00:00');

        $this->assertEquals($date_expected, $date_actual);
    }
}
