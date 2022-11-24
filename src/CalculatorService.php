<?php

namespace Leafpoda\JsonRpcClient;

use Leafpoda\JsonRpcClien\Jsonrpc\Jsonrpc;

class CalculatorService extends Jsonrpc implements ClientInterface
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed|null
     */
    public function __call($method, $arguments)
    {
        return $this->client->$method($arguments);
    }
}
