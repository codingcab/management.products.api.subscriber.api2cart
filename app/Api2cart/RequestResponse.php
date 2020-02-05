<?php


namespace App\Api2cart;


use Psr\Http\Message\ResponseInterface;

class RequestResponse
{

    const RETURN_CODE_OK = 0;
    const RETURN_CODE_MODEL_NOT_FOUND = 112;
    const RETURN_CODE_PRODUCT_SKU_MUST_BE_UNIQUE = 113;

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
     * @return bool
     */
    public function isSuccess()
    {
        return ($this->response->getStatusCode() == 200) && ($this->isReturnCode_OK());
    }

    /**
     * @return bool
     */
    public function isNotSuccess()
    {
        return !$this->isSuccess();
    }

    /**
     * @return mixed
     */
    public function jsonContent()
    {
        return json_decode($this->response_content, false);
    }

    /**
     * @return array
     */
    public function content()
    {
        return json_decode($this->response_content, true);
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

    /**
     * @return bool
     */
    public function isReturnCode_ProductSkuMustBeUnique()
    {
        return $this->returnCode() == self::RETURN_CODE_PRODUCT_SKU_MUST_BE_UNIQUE;
    }
}
