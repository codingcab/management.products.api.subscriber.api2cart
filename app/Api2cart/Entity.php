<?php


namespace App\API2CART;


class Entity
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
        $this->_client = new Client($store_key, $exceptions);
    }

    /**
     * @return Client
     */
    public function client()
    {
        return $this->_client;
    }
}
