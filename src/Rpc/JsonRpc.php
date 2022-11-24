<?php

namespace Leafpoda\JsonRpcClient\Rpc;

use Consul\Services\Agent;
use Exception;
use Leafpoda\JsonRpcClient\Exception\JsonrpcException;

class JsonRpc
{

    /**
     * @var object JsonRPC singleton
     */
    public static object $instance;

    /**
     * @var Client
     */
    protected Client $client;


    /**
     * Singleton instance of JsonRPC.
     *
     * @param string $group Config group name
     * @return object
     * @throws JsonrpcException
     */
    public static function instance(string $group = 'default')
    {
        if (!isset(Jsonrpc::$instance)) {
            // Load the configuration for this group
            // Create a new captcha instance
            Jsonrpc::$instance  = new Jsonrpc($group);
        }
        return Jsonrpc::$instance;
    }

    /**
     * Constructs a new JsonPRC object.
     *
     * @param string Config group name
     * @throws JsonrpcException
     */
    public function __construct($serviceName = null)
    {
        // Create a singleton instance once
        empty(Jsonrpc::$instance) and Jsonrpc::$instance = $this;

        $serviceNameArray=explode('\\',$serviceName);
        $serviceName =array_pop($serviceNameArray);
        // Store the config group name as well, so the drivers can access it
        $class = strtolower( str_replace("Service",'',$serviceName));
        $this->client = new Client((new Consul())->getNode($serviceName), "/$class/", false);
    }

    /**
     * @param $must_have
     * @param $params
     * @return void
     * @throws JsonrpcException
     */
    protected function check_params($must_have, $params)
    {
        $intersect = array_intersect($must_have, array_keys($params));
        if (!(count($must_have) === count($intersect))) {
            throw new JsonrpcException("Missing parameters");
        }
    }



}
