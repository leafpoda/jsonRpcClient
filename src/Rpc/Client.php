<?php
namespace Leafpoda\JsonRpcClient\Rpc;
/*
                    COPYRIGHT

Copyright 2007 Sergio Vaccaro <sergio@inservibile.org>

This file is part of JSON-RPC PHP.

JSON-RPC PHP is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

JSON-RPC PHP is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with JSON-RPC PHP; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

use Leafpoda\JsonRpcClient\Exception\JsonrpcException;


/**
 * The object of this class are generic jsonRPC 1.0 clients
 * http://json-rpc.org/wiki/specification
 *
 * @author sergio <jsonrpcphp@inservibile.org>
 */
class Client {
    
    /**
     * Debug state
     *
     * @var boolean
     */
    private $debug;
    
    /**
     * The server URL
     *
     * @var string
     */
    private $url;

    private $class;
    /**
     * The request id
     *
     * @var integer
     */
    private $id;
    /**
     * If true, notifications are performed instead of requests
     *
     * @var boolean
     */
    private $notification = false;

    /**
     * Takes the connection parameters
     *
     * @param string $url
     * @param $class
     * @param boolean $debug
     */
    public function __construct(string $url, $class, bool $debug = false) {
        // server URL
        $this->url = $url;
        $this->class = $class;
        // proxy
        empty($proxy) ? $this->proxy = '' : $this->proxy = $proxy;
        // debug state
        empty($debug) ? $this->debug = false : $this->debug = true;
        // message id
        $this->id = 1;
    }

    /**
     * Sets the notification state of the object. In this state, notifications are performed, instead of requests.
     *
     * @param boolean $notification
     */
    public function setRPCNotification(bool $notification) {
        empty($notification) ?
                            $this->notification = false
                            :
                            $this->notification = true;
    }

    /**
     * Performs a jsonRCP request and gets the results as an array
     *
     * @param string $method
     * @param array $params
     * @return array|bool
     * @throws JsonrpcException
     */
    public function __call( $method,  $params) {
        
        // check
        if (!is_scalar($method)) {
            throw new JsonrpcException('Method name has no scalar value');
        }
        
        // check
        if (is_array($params)) {
            // no keys
            $params = array_pop($params);
        } else {
            throw new JsonrpcException('Params must be given as array');
        }
        
        // sets notification or request task
        if ($this->notification) {
            $currentId = NULL;
        } else {
            $currentId = $this->id;
        }
        $request = array(
            'method' => $this->class.$method,
            'params' => $params,
            'id' =>(string) $currentId
        );
        try {
            $client = new \GuzzleHttp\Client([
                'handler' => (new Retry())->handler,
            ]);
            $opts = [
                'headers' => [
                    'accept-encoding' => 'gzip, deflate',
                ],
                'json' => $request
            ];
            $response = $client->post($this->url, $opts);
            $response = json_decode($response->getBody()->getContents(), true);
            if (isset($response['error']['message'])) {
                throw new JsonrpcException('Request error: ' . $response['error']['message']);
            }
            return $response['result'];
        }catch (\Throwable $exception){
            throw new JsonrpcException('Request error: ' . $exception->getMessage());
        }
    }
}

