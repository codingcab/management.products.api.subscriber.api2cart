<?php


namespace App\API2CART;


class Entity extends Client
{
    /**
     * @var Client
     */
    private $_client;

    /**
     * Entity constructor.
     * @param string $store_key
     * @param bool $exceptions
     */
    public function __construct(string $store_key, bool $exceptions = true)
    {
        parent::__construct($store_key, $exceptions);
        $this->client = new Client($store_key, $exceptions);
    }

    /**
     * @return Client
     */
    public function client()
    {
        return $this->_client;
    }
}
