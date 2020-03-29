<?php

namespace Ruth\RpcBundle\Http\HttpResponse;

use Ruth\RpcBundle\Controller\JsonRpcController;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonRpcResponseError extends JsonResponse 
{
    /** @var array */
    protected $response;

    /**
     * JsonRpcResponseError constructor.
     * @param int $code
     * @param string|null $error
     */
    public function __construct(int $code, ?string $error = null)
    {
        $this->response = $this->setResponseErrorData($code, $error);
        $headers = [
            'Content-Type' => 'application/json',
        ];
         
        parent::__construct(json_encode($this->response), 200, $headers, true);   
    }

    /**
     * @param string $code
     * @return array
     */
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

    /**
     * @param string $code
     * @param string|null $data
     * @return array
     */
    private function setResponseErrorData(string $code, string $data = null) : array
    {
        $response = [
            'jsonrpc' => '2.0', // 2.0 by default according to specification https://www.jsonrpc.org/specification#response_object
            'error'   => $this->getErrorOnCode($code),
            'id'      => null // null by default according to specification https://www.jsonrpc.org/specification#response_object
        ];

        if ($data != null) {
            $response['error']['data'] = $data;
        }

        $this->response = $response;

        return $response;
    }

    /**
     * @return array
     */
    public function getResponse() : array
    {
        return $this->response;
    }
}