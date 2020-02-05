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
     * @param $notification
     * @param $store_key
     * @param int $store_id
     * @return mixed
     */
    abstract public function handleNotification($notification, $store_key, int $store_id);

    /**
     * @param Request $request
     * @param $store_key
     * @param null $store_id
     * @return mixed
     */
    public function store(Request $request, $store_key, $store_id =  0)
    {
        $content = json_decode($request->getContent(), true);

        logger("SNS Notification Received", $content);

        if ($this->isSubscriptionConfirmation($content)) {
            return $this->subscribe($content);
        }

        return $this->handleNotification($content, $store_key, $store_id);
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
