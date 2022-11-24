<?php
namespace Leafpoda\JsonRpcClien\Rpc;
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

use Leafpoda\JsonRpcClien\Exception\JsonrpcException;


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
    public function __call(string $method, array $params) {
        
        // check
        if (!is_scalar($method)) {
            throw new JsonrpcException('Method name has no scalar value');
        }
        
        // check
        if (is_array($params)) {
            // no keys
            $params = array_values($params);
        } else {
            throw new JsonrpcException('Params must be given as array');
        }
        
        // sets notification or request task
        if ($this->notification) {
            $currentId = NULL;
        } else {
            $currentId = $this->id;
        }
        
        // prepares the request
        $request = array(
            'method' => $this->class.".".$method,
            'params' => $params,
            'id' => $currentId
        );
        $request = json_encode($request);
        $this->debug && $this->debug.='***** Request *****'."\n".$request."\n".'***** End Of request *****'."\n\n";
        // performs the HTTP POST
        $ch = curl_init($this->url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $response = json_decode(curl_exec($ch),true);
        curl_close($ch);
        // debug output
        if ($this->debug) {
            echo nl2br($this->debug);
        }
        // final checks and return
        if (!$this->notification) {
            // check
            if ($response['id'] != $currentId) {
                throw new JsonrpcException('Incorrect response id (request id: '.$currentId.', response id: '.$response['id'].')');
            }
            if (!is_null($response['error']??null)) {
                throw new JsonrpcException('Request error: '.implode(',',$response['error']));
            }
            return $response['result'];
        } else {
            return true;
        }
    }
}
