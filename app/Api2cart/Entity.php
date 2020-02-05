<?php


namespace App\API2CART;


class Entity extends Client
{
    public function __construct(string $store_key, bool $exceptions = true)
    {
        parent::__construct($store_key, $exceptions);
    }
}
