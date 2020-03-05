<?php

namespace App\Http\Controllers;

use App\Api2cart\Products;
use App\Http\Requests\ProductsPostRequest;
use App\Jobs\SyncProductJob;
use App\Jobs\VerifyProductSyncJob;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ProductsController extends SnsController
{
    const ALLOWED_KEYS = [
        "sku",
        "name",
        "price",
        "sale_price",
        "sale_price_start_date",
        "sale_price_end_date",
        "quantity_available"
    ];

    /**
     * @param ProductsPostRequest $request
     * @param string $store_key
     * @param int $store_id
     * @return JsonResponse
     * @throws Exception
     */
    public function store(ProductsPostRequest $request, string $store_key, int $store_id =  0)
    {
        info("SKU Update Request", $request->all());

        if ($this->isSubscriptionConfirmation($request->all())) {
            return $this->subscribe($request->all());
        }

        $product_data =  $request->only( self::ALLOWED_KEYS);

        SyncProductJob::dispatch($store_key, $product_data);

        return $this->respond_200_OK();
    }

}
