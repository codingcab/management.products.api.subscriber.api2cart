<?php


namespace App\Api2cart;


use Psr\Http\Message\ResponseInterface;

class Api2CartResponse
{
    private $response;

    /**
     * Api2CartResponse constructor.
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }
}
