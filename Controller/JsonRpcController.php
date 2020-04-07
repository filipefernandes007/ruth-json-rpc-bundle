<?php

namespace Ruth\RpcBundle\Controller;

use PHPUnit\Util\Json;
use Ruth\RpcBundle\Http\HttpRequest\JsonRpcRequest;
use Ruth\RpcBundle\Http\HttpRequest\JsonRpcRequestException;
use Ruth\RpcBundle\Http\HttpResponse\JsonRpcResponse;
use Ruth\RpcBundle\Http\HttpResponse\JsonRpcResponseError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Invoker\Invoker;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class JsonRpcController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const INVALID_REQUEST  = -32600;
    const METHOD_NOT_FOUND = -32601;
    const INVALID_PARAMS   = -32602;
    const INTERNAL_ERROR   = -32603;
    const PARSE_ERROR      = -32700;
    
    /** @var bool */
    public $disableProfiler = true;

    /** @var LoggerInterface */
    protected $logger;

    /** @var JsonRpcRequest */
    protected $jsonRpcRequest;

    /** @var array */
    protected $result;

    /** @var string|int|null */
    protected $id;

    /** @var bool */
    protected $yieldBatch = false;

    public function __construct(LoggerInterface $logger) 
    {    
        $this->logger = $logger;    
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function execute(Request $request) : JsonResponse
    {
        if ($this->disableProfiler && $this->container->has('profiler')) {
            $this->container->get('profiler')->disable();
        }

        $content = $request->getContent();

        try {
            $data = json_decode(
                $content,
                false,
                $depth = 512,
                JSON_THROW_ON_ERROR,
            );
        } catch(\Exception $e) {
            return $this->errorResponseWithLog(
                $request,
                self::PARSE_ERROR,
                "Invalid request",
            );
        }

        if (is_array($data)) {
            $result = [];

            if (!$this->yieldBatch) {
                $result = $this->batchProcess(
                    $request,
                    $data
                );
            } else {
                $result = iterator_to_array(
                    $this->batchProcessGenerator(
                        $request,
                        $data
                    )
                );
            }

            return new JsonResponse(
                $result,
                200
            );
        } else {
            return $this->rpcCall($request);
        }
    }

    /**
     * @param Request $request
     * @param array $data
     * @return array|\stdClass[]
     */
    protected function batchProcess(Request $request, array $data) : array
    {
        $result = [];

        foreach($data as $value) {
            $tmpRequest = Request::create(
                $request->getUri(),
                $request->getMethod(),
                $request->request->all(),
                [],
                [],
                [],
                json_encode($value)
            );

            $result[] = json_decode($this->rpcCall($tmpRequest)->getContent());
        }

        return $result;
    }

    /**
     * @param Request $request
     * @param array $data
     * @return \Generator
     */
    protected function batchProcessGenerator(Request $request, array $data) : \Generator
    {
        $result = [];

        foreach($data as $value) {
            $tmpRequest = Request::create(
                $request->getUri(),
                $request->getMethod(),
                $request->request->all(),
                [],
                [],
                [],
                json_encode($value)
            );

            yield ($result[] = json_decode($this->rpcCall($tmpRequest)->getContent()));
        }

        return $result;
    }

    /**
     * @param Request $request
     * @return JsonRpcResponse
     */
    protected function rpcCall(Request $request) : JsonResponse
    {
        $this->logRequest($request);

        try {
            /** @var array $data */
            $data = json_decode($request->getContent(), true);

            if (isset($data['id'])) {
                $this->id = $data['id'];
            }

            $this->jsonRpcRequest = new JsonRpcRequest($request);
            $serviceInstance = $this->callService();
        } catch(JsonRpcRequestException $e) {
            return $this->errorResponseWithLog($request, self::PARSE_ERROR, $e->getMessage());
        } catch(ServiceNotFoundException $e) {
            return $this->errorResponseWithLog($request, self::METHOD_NOT_FOUND);
        } catch(\ErrorException $e) {
            return $this->errorResponseWithLog($request, self::INVALID_PARAMS);
        }

        if (!\is_callable(array($serviceInstance, $this->jsonRpcRequest->getMethod()))) {
            return $this->errorResponseWithLog($request, self::METHOD_NOT_FOUND);
        }

        try {
            //$result = call_user_func_array(array($serviceInstance, $method), $params);
            $invoker = new Invoker();
            $this->result = $invoker->call(
                [$serviceInstance, $this->jsonRpcRequest->getMethod()],
                $this->jsonRpcRequest->getParams()
            );
        } catch(\Exception $e) {
            return $this->errorResponseWithLog($request, self::INTERNAL_ERROR, $e->getMessage());
        }

        $this->logResponse($request, null);

        if ($this->id === null) {
            // It's a notification: no response should be returned.
            return new JsonRpcResponse(null);
        }

        return new JsonRpcResponse($this->jsonRpcRequest->getId(), $this->result);
    }

    /**
     * @return object|null
     */
    protected function callService() : ?object
    {
        return $this->container->get($this->jsonRpcRequest->getService());
    }

    /**
     * @param Request $request
     */
    protected function logRequest(Request $request) : void
    {
        $this->logger->info('==>', 
            [
                'verb'         => $request->getMethod(),
                'url'          => $this->getUrl($request),
                'request-data' => $this->requestData($request),
            ]
        );
    }

    /**
     * @param Request $request
     * @param array|null $result
     */
    protected function logResponse(Request $request, ?array $result) : void
    {
        $this->logger->info('<==', 
            [
                'verb'         => $request->getMethod(),
                'url'          => $this->getUrl($request),
                'result'       => $this->result != null ? $this->result : $result,
                'request-data' => $this->requestData($request),
            ]
        );
    }

    /**
     * @param Request $request
     * @param int $code
     * @param string|null $data
     * @return JsonRpcResponseError
     */
    protected function errorResponseWithLog(Request $request, int $code, ?string $data = null) : JsonRpcResponseError
    {
        $response = new JsonRpcResponseError($code, $data);
        $this->logResponse($request, $response->getResponse());
        return $response;
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function getUrl(Request $request) : string 
    {
        return $request->getSchemeAndHttpHost() . $request->getPathInfo();
    }

    /**
     * @param Request $request
     * @return array|null
     */
    protected function requestData(Request $request) : ?array
    {
        $data = null;

        if (count($request->request->keys()) > 0) {
            $data = $request->request->keys()[0];
        }
        
        // replace underscore that sometimes appears instead of space
        $data = preg_replace('/,*_+"/', ',"', $data);

        return json_decode($data, true);
    }

    /**
     * @return bool
     */
    public function isYieldBatch() : bool
    {
        return $this->yieldBatch;
    }

    /**
     * @param bool $yieldBatch
     * @return JsonRpcController
     */
    public function setYieldBatch(bool $yieldBatch) : JsonRpcController
    {
        $this->yieldBatch = $yieldBatch;
        return $this;
    }
}
