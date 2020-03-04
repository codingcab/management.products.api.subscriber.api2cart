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

        logger("SNS Notification Received", $notification);

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
        $product_data = $this->generateProductData($notification);

        $product_data['store_id'] = $store_id;

        SyncProductJob::dispatch($store_key, $product_data);

        return $this->respond_200_OK();
    }

    /**
     * @param array $notification
     * @return array
     */
    private function generateProductData(array $notification)
    {
        $product = [];

        $product["sku"]             = $notification["sku"];
        $product["model"]           = $notification["sku"];
        $product["name"]            = $notification["name"];
        $product["description"]     = $notification["name"];
        $product["price"]           = $notification["price"];
        $product["special_price"]   = $notification["sale_price"];
        $product["sprice_create"]   = $notification["sale_price_start_date"];
        $product["sprice_expire"]   = $notification["sale_price_end_date"];

        $product["quantity"]        = intval($notification["quantity_available"]);

        if ($product["quantity"] > 0) {
            $product["in_stock"] = true;
        }

        if (Arr::has($notification, "store_id")) {
            $product["store_id"] = $notification["store_id"];
        }

        return $product;
    }
}
