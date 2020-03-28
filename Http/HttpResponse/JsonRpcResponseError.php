<?php

namespace Ruth\RpcBundle\Http\HttpResponse;

use Ruth\RpcBundle\Controller\JsonRpcController;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonRpcResponseError extends JsonResponse 
{
    protected $response;

    public function __construct(string $code, ?string $id, $data = null) 
    {
        $this->response = $this->setResponseData($code, $id, $data);
        $headers = [
            'Content-Type' => 'application/json',
        ];
         
        parent::__construct(json_encode($this->response), 200, $headers, true);   
    }

    protected function getErrorOnCode(string $code) : array 
    {
        $message = '';

        switch ($code) {
            case JsonRpcController::PARSE_ERROR:
                $message = 'Parse error';
                break;
            case JsonRpcController::INVALID_REQUEST:
                $message = 'Invalid request';
                break;
            case JsonRpcController::METHOD_NOT_FOUND:
                $message = 'Method not found';
                break;
            case JsonRpcController::INVALID_PARAMS:
                $message = 'Invalid params';
                break;
            case JsonRpcController::INTERNAL_ERROR:
                $message = 'Internal error';
                break;
        }

        return ['code' => $code, 'message' => $message];
    }

    public function setResponseData(string $code, ?string $id, $data = null)
    {
        if (\is_array($data) || \is_object($data)) {
            $data = json_encode($data);
        }

        $response = array('jsonrpc' => '2.0');
        $response['error'] = $this->getErrorOnCode($code);
        $response['id']    = $id !== null ? $id : null;

        if ($data != null) {
            $response['error']['data'] = $data;
        }

        $this->reponse = $response;

        return $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}