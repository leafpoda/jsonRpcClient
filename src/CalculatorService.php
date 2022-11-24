<?php

namespace Leafpoda\JsonRpcClient;

use Leafpoda\JsonRpcClient\JsonRpc;

class CalculatorService extends JsonRpc implements ClientInterface
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
