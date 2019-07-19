<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AddProductController extends SNSController
{
    function handleNotification($notification)
    {
        Log::info('Dealing with notification in add product controller');
    }
}
