<?php

namespace Ruth\RpcBundle\Http\HttpRequest;

use Symfony\Component\HttpFoundation\Request;

class JsonRpcRequest {
    protected $originalRequestData;
    protected $service;
    protected $method;
    protected $params;
    protected $id;

    public function __construct(Request $request)
    {
        $this->originalRequestData = json_decode($request->getContent(), true);

        if ($this->originalRequestData === null) { 
            throw new JsonRpcRequestException("Request content is null");
        }

        if (isset($this->originalRequestData['id'])) {
            $this->id = $this->originalRequestData['id'];
        }

        if (!$request->isMethod('post')) {
            throw new JsonRpcRequestException("Invalid Request: verb should be POST");
        }

        if (!isset($this->originalRequestData['jsonrpc']) || !isset($this->originalRequestData['method'])) {
            throw new JsonRpcRequestException("Invalid Request");
        }

        if ((int) $this->originalRequestData['jsonrpc'] < 2) {
            throw new JsonRpcRequestException("Invalid Request: json-rpc version is lower than 2.0");
        }

        $this->method = $this->originalRequestData['method'];
        
        $explode = explode(':', $this->method);

        if (count($explode) === 0) {
            throw new JsonRpcRequestException("Could not extract service and method");
        }

        if (count($explode) < 2) {
            throw new JsonRpcRequestException("Could not extract method");
        }

        $this->params  = isset($this->originalRequestData['params']) ? $this->originalRequestData['params'] : [];
        $this->service = $explode[0];
        $this->method  = $explode[1];
    }

    /** 
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /** 
     * @return array|null
     */
    public function getParams() : ?array
    {
        return $this->params;
    }

    /** 
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /** 
     * @return string
     */
    public function getService() : string
    {
        return $this->service;
    }
}