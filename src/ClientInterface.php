<?php

namespace Leafpoda\JsonRpcClient;

interface ClientInterface
{
    public function __construct();

    public function __call($method, $arguments);
}
