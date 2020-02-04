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

        // used casting because PSR-7
        // https://stackoverflow.com/questions/30549226/guzzlehttp-how-get-the-body-of-a-response-from-guzzle-6
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
