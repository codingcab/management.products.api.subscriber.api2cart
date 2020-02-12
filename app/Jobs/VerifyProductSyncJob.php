<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class VerifyProductSyncJob
 * @package App\Jobs
 */
class VerifyProductSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string|null
     */
    private $_store_key = null;

    /**
     * @var array|null
     */
    private $_product_data = null;

    /**
     * Create a new job instance.
     *
     * @param string $store_key
     * @param array $product_data
     */
    public function __construct(string $store_key, array $product_data)
    {
        $this->_store_key = $store_key;
        $this->_product_data = $product_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        info('We should verify product update here', $this->_product_data);
    }
}
