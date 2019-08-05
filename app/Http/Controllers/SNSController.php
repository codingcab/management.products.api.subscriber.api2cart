<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SNSController extends Controller
{

    public function handleRequest(Request $request, $storekey) {

        $requestJSON = json_decode($request->getContent(), true);

        if($requestJSON['Type'] == 'SubscriptionConfirmation') {

            return $this->subscribe($requestJSON);

        }

        return $this->handleNotification($requestJSON, $storekey);

    }


    private function subscribe($notification) {

        $guzzleClient = new \GuzzleHttp\Client();

        $guzzleResponse = $guzzleClient->get($notification['SubscribeURL']);

        return app('Illuminate\Http\Response')->status();

    }


    function handleNotification($notification, $storekey){
        //Handled in ProductsController.php
    }
}
