<?php

namespace App\Http\Controllers;

use BadMethodCallException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class NotImplementedException extends BadMethodCallException
{}

abstract class BaseSnsController extends Controller
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
     * @param $notification
     * @return mixed
     */
    private function subscribe($notification)
    {
        info("Subscribing to topic");

        $guzzleClient = new \GuzzleHttp\Client();

        $guzzleResponse = $guzzleClient->get($notification['SubscribeURL']);

        return app('Illuminate\Http\Response')->status();
    }

    /**
     * @param $content
     * @return bool
     */
    private function isSubscriptionConfirmation($content): bool
    {
        return Arr::has($content, 'Type') && ($content['Type'] == 'SubscriptionConfirmation');
    }

}
