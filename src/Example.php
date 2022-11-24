<?php

namespace Leafpoda\JsonRpcClient;

class Example
{

    public function index()
    {
        (new CalculatorService())->add(1, 2);
    }
}
