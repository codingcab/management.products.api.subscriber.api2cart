<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
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
     * @return mixed
     */
    abstract public function handleIncomingNotification(array $notification, string $store_key, int $store_id);

    /**
     * @param Request $request
     * @param string $store_key
     * @param int $store_id
     * @return mixed
     */
    public function store(Request $request, string $store_key, int $store_id =  0)
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
     * @return mixed
     */
    private function subscribe(array $notification)
    {
        info("Subscribing to topic");

        $guzzleClient = new \GuzzleHttp\Client();

        $guzzleResponse = $guzzleClient->get($notification['SubscribeURL']);

        return app('Illuminate\Http\Response')->status();
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
     * @return \Illuminate\Http\JsonResponse
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
     */
    public function respond_200_OK(string $message = '') {
        $this->respond($message, 200);
    }

    /**
     * @param string $message
     */
    public function respond_404_NoFound(string $message = 'Not Found') {
        $this->respond($message, 404);
    }

}
