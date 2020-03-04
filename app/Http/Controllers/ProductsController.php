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
    /**
     * @param ProductsPostRequest $request
     * @param string $store_key
     * @param int $store_id
     * @return JsonResponse
     * @throws Exception
     */
    public function store(ProductsPostRequest $request, string $store_key, int $store_id =  0)
    {
        $notification = json_decode($request->getContent(), true);

        info("Product Update Request Received", $notification);

        if ($this->isSubscriptionConfirmation($notification)) {
            return $this->subscribe($notification);
        }

        return $this->handleIncomingNotification($notification, $store_key, $store_id);
    }

    /**
     * @param array $notification
     * @param string $store_key
     * @param int $store_id
     * @return JsonResponse
     * @throws Exception
     */
    public function handleIncomingNotification(array $notification, string $store_key, int $store_id)
    {
        $allowed_values = [
            "sku",
            "name",
            "price",
            "sale_price",
            "sale_price_start_date",
            "sale_price_end_date"
        ];

        $product_data =  Arr::only($notification, $allowed_values);

        $product_data['store_id'] = $store_id;

        SyncProductJob::dispatch($store_key, $product_data);

        return $this->respond_200_OK();
    }

}
