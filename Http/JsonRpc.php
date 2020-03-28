<?php

namespace Ruth\RpcBundle\Http;

class JsonRpc implements \JsonSerializable {
    protected $service;
    protected $method;
    protected $params;
    protected $id;
    protected $version;

    public function __construct(
        ?string $method,
        array $params,
        ?string $id,
        string $version = "2.0"
    ) {
        $this->method  = $method;
        $this->params  = $params;
        $this->id      = $id;
        $this->version = $version;
    }

    public function getService() : ?string 
    {
        return $this->service;
    }

    public function getMethod() : ?string 
    {
        return $this->method;
    }

    public function getParams() : array
    {
        return $this->params;
    }

    public function getId() : ?string 
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getVersion() : string
    {
        return $this->version;
    }

    public function jsonSerialize() {
        return [
            'jsonrpc' => $this->version,
            'method'  => $this->getMethod(),
            'id'      => $this->getId(),
            'params'  => $this->getParams(),
        ];
    }
}
