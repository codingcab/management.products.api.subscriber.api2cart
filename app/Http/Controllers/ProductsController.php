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
     * @param Request $request
     * @param string $store_key
     * @param int $store_id
     * @return JsonResponse
     * @throws Exception
     */
    public function store(Request $request, string $store_key, int $store_id =  0)
    {
        $content = json_decode($request->getContent(), true);

        logger("Product Request Received", $content);

        if ($this->isSubscriptionConfirmation($content)) {
            return $this->subscribe($content);
        }

        $product_data = Arr::only($content, self::ALLOWED_KEYS);

        $product_data["store_id"] = $store_id;

        SyncProductJob::dispatch($store_key, $product_data);

        return $this->respond_200_OK();
    }

}
