<?php

namespace Ruth\RpcBundle\Tests;

use Ruth\RpcBundle\Http\HttpRequest\JsonRpcRequest;
use Ruth\RpcBundle\Http\HttpRequest\JsonRpcRequestException;
use Ruth\RpcBundle\Http\HttpResponse\JsonRpcResponse;
use Ruth\RpcBundle\Service\ServiceMediator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

class JsonRpcRequestTest extends \PHPUnit\Framework\TestCase
{
    protected $containerMock;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testFailContentEmpty()
    {
        // Let's create a request
        $request = Request::create(
            '/test',
            'POST',
            [],
            [],
            [],
            [],
            null
        );

        $this->expectException(JsonRpcRequestException::class);
        $this->expectExceptionMessage('Request content is null');

        new JsonRpcRequest($request);
    }

    public function testFailWrongExpectedVerb()
    {
        $data = '{"jsonrpc":"2.0","method":"ruth_rpc.service_test:foo","id":"2957f28d-8797-42b1-bd5d-45834b3202d","params":{"x":1,"y":2}}';

        // Let's create a request
        $request = Request::create(
            '/test',
            'PUT',
            [],
            [],
            [],
            [],
            $data
        );

        $this->expectException(JsonRpcRequestException::class);
        $this->expectExceptionMessage('Invalid Request: verb should be POST');

        new JsonRpcRequest($request);
    }

    public function testFailExpectedJsonVersion()
    {
        $data = '{"method":"ruth_rpc.service_test:foo","id":"2957f28d-8797-42b1-bd5d-45834b3202d","params":{"x":1,"y":2}}';

        // Let's create a request
        $request = Request::create(
            '/test',
            'POST',
            [],
            [],
            [],
            [],
            $data
        );

        $this->expectException(JsonRpcRequestException::class);
        $this->expectExceptionMessage('Invalid Request');

        new JsonRpcRequest($request);
    }

    public function testFailExpectedMethod()
    {
        $data = '{"jsonrpc":"2.0","id":"2957f28d-8797-42b1-bd5d-45834b3202d","params":{"x":1,"y":2}}';

        // Let's create a request
        $request = Request::create(
            '/test',
            'POST',
            [],
            [],
            [],
            [],
            $data
        );

        $this->expectException(JsonRpcRequestException::class);
        $this->expectExceptionMessage('Invalid Request');

        new JsonRpcRequest($request);
    }

    public function testFailWrongJsonVersion()
    {
        $data = '{"jsonrpc":"1.0","method":"ruth_rpc.service_test:foo","id":"2957f28d-8797-42b1-bd5d-45834b3202d","params":{"x":1,"y":2}}';

        // Let's create a request
        $request = Request::create(
            '/test',
            'POST',
            [],
            [],
            [],
            [],
            $data
        );

        $this->expectException(JsonRpcRequestException::class);
        $this->expectExceptionMessage('Invalid Request: json-rpc version is not 2.0');

        new JsonRpcRequest($request);
    }

    public function testFailCouldNotExtractServiceAndMethod()
    {
        $data = '{"jsonrpc":"2.0","method":"ruth_rpc.service_test__foo","id":"2957f28d-8797-42b1-bd5d-45834b3202d","params":{"x":1,"y":2}}';

        // Let's create a request
        $request = Request::create(
            '/test',
            'POST',
            [],
            [],
            [],
            [],
            $data
        );

        $this->expectException(JsonRpcRequestException::class);
        $this->expectExceptionMessage('Could not extract method');

        new JsonRpcRequest($request);
    }

    public function testFailNoServiceMethod()
    {
        $data = '{"jsonrpc":"2.0","method":"ruth_rpc.service_test","id":"2957f28d-8797-42b1-bd5d-45834b3202d","params":{"x":1,"y":2}}';

        // Let's create a request
        $request = Request::create(
            '/test',
            'POST',
            [],
            [],
            [],
            [],
            $data
        );

        $this->expectException(JsonRpcRequestException::class);
        $this->expectExceptionMessage('Could not extract method');

        new JsonRpcRequest($request);
    }

    public function testSuccess()
    {
        $data = '{"jsonrpc":"2.0","method":"ruth_rpc.service_test:foo","id":"2957f28d-8797-42b1-bd5d-45834b3202d","params":{"x":1,"y":2}}';

        // Let's create a request
        $request = Request::create(
            '/test',
            'POST',
            [],
            [],
            [],
            [],
            $data
        );

        $request = new JsonRpcRequest($request);

        $this->assertEquals('ruth_rpc.service_test', $request->getService());
        $this->assertEquals('foo', $request->getMethod());
        $this->assertEquals(['x'=>1, 'y'=>2], $request->getParams());
    }
}