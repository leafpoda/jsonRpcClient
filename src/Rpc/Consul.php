<?php

namespace Leafpoda\JsonRpcClient\Rpc;

use Consul\Services\Agent;
use Consul\Services\Health;
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
            //获取健康的services的id
            $agent = new Health();
            $checks = $agent->state('passing')->getBody();
            $checks = json_decode($checks,true);
            $passServiceId = array_column($checks,'ServiceID');
            //获取services详细信息
            $agent = new Agent();
            $services = $agent->services()->getBody();
            $services = json_decode($services,true);
            $nodes = [];
            foreach ($services as $serviceId => $service){
                if ($service['Service']==$serviceName && in_array($serviceId,$passServiceId)){
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
