<?php


namespace App\Api2cart;


use Psr\Http\Message\ResponseInterface;

class Api2CartResponse
{

    const RETURN_CODE_OK = 0;
    const RETURN_CODE_MODEL_NOT_FOUND = 112;

    /**
     * @var ResponseInterface
     */
    private $response;
    /**
     * @var string
     */
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

    /**
     * @return bool
     */
    public function isReturnCode_OK()
    {
        return $this->returnCode() == self::RETURN_CODE_OK;
    }

    /**
     * @return bool
     */
    public function isReturnCode_ModelNotFound()
    {
        return $this->returnCode() == self::RETURN_CODE_MODEL_NOT_FOUND;
    }
}
