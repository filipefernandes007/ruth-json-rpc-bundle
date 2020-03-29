<?php

namespace Ruth\RpcBundle\Http\HttpResponse;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonRpcResponse extends JsonResponse 
{
    /**
     * JsonRpcResponse constructor.
     * @param string|null $id
     * @param mixed|null $data
     */
    public function __construct(?string $id, $data = null) 
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        // id null or empty? it's a notification: https://www.jsonrpc.org/specification#notification
        if (empty($id)) {
            $response = '';
        } else {
            $response = json_encode([
                'jsonrpc' => '2.0',
                'result'  => $data,
                'id'      => $id
            ]);
        }

        parent::__construct($response, 200, $headers, true);
    }
}
