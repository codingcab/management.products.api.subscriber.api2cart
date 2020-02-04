<?php


namespace App\Api2cart;


use Psr\Http\Message\ResponseInterface;

class Api2CartResponse
{
    private $response;
    private $response_content;

    /**
     * Api2CartResponse constructor.
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
        $this->response_content = $response->getBody()->getContents();
    }

    /**
     * @return mixed
     */
    public function jsonContent()
    {
        return json_decode($this->response_content, false);
    }

    /**
     * @return int
     */
    public function returnCode()
    {
        return $this->jsonContent()->return_code;
    }
}
