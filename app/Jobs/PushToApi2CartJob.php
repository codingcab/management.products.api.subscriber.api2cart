<?php

namespace App\Jobs;

use App\Api2cart\Products;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class PushToApi2CartJob implements ShouldQueue
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
     * @throws Exception
     */
    public function handle()
    {
        $api2cart_new = new Products($this->_store_key);

        $response = $api2cart_new->updateOrCreate($this->_product_data);

        if($response->isNotSuccess()) {
            Log::error('Could not update Product', $this->_product_data);
            Log::error('Received API2CART Response', $response->content());
            throw new Exception('Could not update Product');
        }

        Log::info('Product synced', $this->_product_data);
    }

    public function failed(Exception $exception)
    {
        Log::error('Job failed', $this->_product_data);
    }
}
