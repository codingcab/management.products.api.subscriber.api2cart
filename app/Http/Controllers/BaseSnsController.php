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

        Log::info('Received SNS notification');

        $requestJSON = json_decode($request->getContent(), true);

        if (array_has($requestJSON, 'Type') && ($requestJSON['Type'] == 'SubscriptionConfirmation') ) {
            return $this->subscribe($requestJSON);
        }

        if (isset($store_id) && ($store_id != 0)) {
            $requestJSON['store_id'] = $store_id;
        }

        return $this->handleNotification($requestJSON, $store_key);
    }


    private function subscribe($notification)
    {
        info("Subscribing to topic");

        $guzzleClient = new \GuzzleHttp\Client();

        $guzzleResponse = $guzzleClient->get($notification['SubscribeURL']);

        return app('Illuminate\Http\Response')->status();

    }

}
