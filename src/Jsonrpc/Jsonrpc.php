<?php

namespace Leafpoda\JsonRpcClien\Jsonrpc;


use Exception;
use JsonrpcException;

class Jsonrpc
{

    /**
     * @var object JsonRPC singleton
     */
    public static $instance;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array Default config values
     */
    public static $config = array(
        'HOST' => '127.0.0.1',
        'PORT' => 8084,
        'PATH' => '/api',
        'CLASS' => 'ApiService',
    );

    /**
     * Singleton instance of JsonRPC.
     *
     * @param string $group Config group name
     * @return object
     * @throws JsonrpcException
     */
    public static function instance($group = 'default')
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
    public function __construct($group = null)
    {
        // Create a singleton instance once
        empty(Jsonrpc::$instance) and Jsonrpc::$instance = $this;

        // No config group name given
        if (!is_string($group)) {
            $group = 'default';
        }

        // Load and validate config group
        if (!is_array($config = config("jsonrpc.$group"))) {
            throw new JsonrpcException(
                'jsonrpc group not defined in :group configuration',
                array(':group' => $group)
            );
        }

        // All jsonprc config groups inherit default config group
        if ($group !== 'default') {
            // Load and validate default config group
            if (!is_array($default = config("jsonrpc.default"))) {
                throw new JsonrpcException(
                    'jsonrpc group not defined in :group configuration',
                    array(':group' => 'default')
                );
            }

            // Merge config group with default config group
            $config += $default;
        }

        // Assign config values to the object
        foreach ($config as $key => $value) {
            if (array_key_exists($key, Jsonrpc::$config)) {
                Jsonrpc::$config[$key] = $value;
            }
        }

        // Store the config group name as well, so the drivers can access it
        Jsonrpc::$config['group'] = $group;
        $addr = sprintf("http://%s:%u%s", Jsonrpc::$config['HOST'], Jsonrpc::$config['PORT'], Jsonrpc::$config['PATH']);
        $this->client = new Client($addr, Jsonrpc::$config['CLASS'], false);
    }

    protected function check_params($must_have, $params)
    {
        $intersect = array_intersect($must_have, array_keys($params));
        return count($must_have) === count($intersect);
    }

    protected function decode_result($recv)
    {
        if (!isset($recv['Message'])) {
            return null;
        }
        $res = json_decode($recv['Message']);
        if (!empty($res->Vars)) {
            $res->Vars = json_decode($res->Vars, true);
        }
        return $res;
    }

    /**
     * @param $params
     * @param $app
     * @param $timeout
     * @return mixed|null
     * @throws JsonrpcException
     */
    public function get_delegate_reports($params, $app, $timeout = 0)
    {
        $must_have = array('Session', 'Nick', 'AccountId', 'DelegateId', 'SubwayToken');
        if (!$this->check_params($must_have, $params)) {
            throw new JsonrpcException("Missing parameters");
        }
        foreach ($params as $k => $v) {
            if (is_numeric($v)) {
                $params[$k] = (int)$v;
            }
        }
        if (!isset($params['Force'])) {
            $params['Force'] = true;
        }
        $params['AnApiKey'] = array('Key' => $app['key'], 'Secret' => $app['secret']);
        $msg = array('Vars' => json_encode($params));
        $retries = 3;
        while ($retries > 0) {
            try {
                $recv = $this->client->GetDelegateReports($msg);
                return $this->decode_result($recv);
            } catch (Exception $e) {
                $retries--;
            }
        }
        return null;
    }
}