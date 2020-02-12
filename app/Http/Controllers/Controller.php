<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $statusCode = 0;

    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }

    public function respond_ok_200($message = '') {
        $this->setStatusCode(200)
            ->respond($message);
    }

    public function respond_NoFound_404($message = 'Not Found') {
        $this->setStatusCode(404)
            ->respond($message);
    }
}
