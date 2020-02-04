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

    /**
     * @return mixed
     */
    public function jsonContent()
    {
        return json_decode($this->response->getBody()->getContents(), false);
    }
}
