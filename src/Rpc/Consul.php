<?php

namespace Leafpoda\JsonRpcClient\Rpc;

use Consul\Services\Agent;
use Exception;
use Leafpoda\JsonRpcClient\Exception\JsonrpcException;

class Consul
{

    /**
     * @param $serviceName
     * @return string
     * @throws JsonrpcException
     */
    public function getNode($serviceName): string
    {
        try {
            $kv = new Agent();
            $services = $kv->services()->getBody();
            $services = json_decode($services,true);
            $nodes = [];
            foreach ($services as $service){
                if ($service['Service']==$serviceName){
                    $nodes[] = $service;
                }
            }
            if (!$nodes){
                throw  new JsonrpcException("NOT FOUND nodes");
            }
            $checkNode = array_rand($nodes);
            return sprintf("http://%s:%u", $nodes[$checkNode]['Address'], $nodes[$checkNode]['Port']);
        }catch (Exception $exception){
            throw  new JsonrpcException($exception->getMessage());
        }
    }



}
