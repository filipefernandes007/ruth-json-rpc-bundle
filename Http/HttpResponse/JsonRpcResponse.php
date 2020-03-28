<?php

namespace Ruth\RpcBundle\Http\HttpResponse;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonRpcResponse extends JsonResponse 
{
    public function __construct(?string $id, $data = null) 
    {
        $response = [
            'jsonrpc' => '2.0',
            'result' => $data,
            'id' => $id
        ];

        $headers = [
            'Content-Type' => 'application/json',
        ];
         
        parent::__construct(json_encode($response), 200, $headers, true);
    }
}