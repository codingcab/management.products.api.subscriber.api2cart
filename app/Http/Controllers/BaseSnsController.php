<?php

namespace App\Http\Controllers;

use BadMethodCallException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotImplementedException extends BadMethodCallException
{}

abstract class BaseSnsController extends Controller
{
    abstract public function handleNotification($notification, $store_key);

    public function store(Request $request, $store_key, $store_id =  null) {

        $content = json_decode($request->getContent(), true);

        logger("SNS Notification Received", $content);

        if ($this->isSubscriptionConfirmation($content)) {
            return $this->subscribe($content);
        }

        if (isset($store_id) && ($store_id != 0)) {
            $content['store_id'] = $store_id;
        }

        return $this->handleNotification($content, $store_key);
    }


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
        return array_has($content, 'Type') && ($content['Type'] == 'SubscriptionConfirmation');
    }

}
