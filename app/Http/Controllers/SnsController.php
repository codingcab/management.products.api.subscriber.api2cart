<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

abstract class SnsController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param array $notification
     * @param string $store_key
     * @param int $store_id
     * @return JsonResponse
     */
    abstract public function handleIncomingNotification(array $notification, string $store_key, int $store_id);

    /**
     * @param array $notification
     * @return JsonResponse
     */
    private function subscribe(array $notification)
    {
        info("Subscribing to topic");

        $guzzleClient = new \GuzzleHttp\Client();

        $guzzleResponse = $guzzleClient->get($notification['SubscribeURL']);

        return $this->respond_200_OK('Subscribed to topic successfully');
    }

    /**
     * @param array $notification
     * @return bool
     */
    private function isSubscriptionConfirmation(array $notification): bool
    {
        return Arr::has($notification, 'Type') && ($notification['Type'] == 'SubscriptionConfirmation');
    }

    /**
     * @param string $message
     * @param int $status_code
     * @return JsonResponse
     */
    public function respond(string $message = '', int $status_code = 200) {
        return response()->json(
            [
                'message' => $message,
                'error_id' => null,
            ],
            $status_code,
            []
        );
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    public function respond_200_OK(string $message = '') {
        return $this->respond($message, 200);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    public function respond_404_NoFound(string $message = 'Not Found') {
        return $this->respond($message, 404);
    }

}
