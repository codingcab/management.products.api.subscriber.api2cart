<?php

namespace App\Http\Controllers;

use BadMethodCallException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class NotImplementedException extends BadMethodCallException
{}

abstract class BaseSnsController extends BaseController
{
    /**
     * @param array $notification
     * @param string $store_key
     * @param int $store_id
     * @return mixed
     */
    abstract public function handleNotification(array $notification, string $store_key, int $store_id);

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

        return $this->handleNotification($notification, $store_key, $store_id);
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
     */
    public function respond(string $message = '', int $status_code = 200) {
        $response = response()->json(
            [
                'message' => $message,
                'error_id' => null,
            ],
            $status_code,
            []
        );

        $response->throwResponse();
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
