<?php

namespace Leafpoda\JsonRpcClient\Rpc;

use Consul\Services\Agent;
use Exception;
use Leafpoda\JsonRpcClien\Exception\JsonrpcException;

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
     * @var array Default config values
     */
    public static array $config = array(
        'HOST' => '127.0.0.1',
        'PORT' => 8084,
        'PATH' => '/'
    );

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
        try {
            $kv = new Agent();
            $services = $kv->services()->getBody();
            $services = json_decode($services,true);
            $services = array_column($services,null,"Service");
            if (!isset($services[$serviceName])){
                throw  new JsonrpcException("NOT FOUND SERVICE");
            }
            Jsonrpc::$config['PORT'] = $services[$serviceName]['Port'];
            Jsonrpc::$config['HOST'] =$services[$serviceName]['Address'];
        }catch (Exception $exception){
            throw  new JsonrpcException($exception->getMessage());
        }
        // Store the config group name as well, so the drivers can access it
        $class = strtolower( str_replace("Service",'',$serviceName));
        $addr = sprintf("http://%s:%u%s", Jsonrpc::$config['HOST'], Jsonrpc::$config['PORT'], Jsonrpc::$config['PATH']);
        $this->client = new Client($addr, "/$class/", false);
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
