<?php

namespace Ruth\RpcBundle\Tests\Integration;

use Psr\Log\LoggerInterface;
use Ruth\RpcBundle\Http\HttpResponse\JsonRpcResponseError;
use Ruth\RpcBundle\Controller\JsonRpcController;
use Ruth\RpcBundle\Controller\VictoriousPuppyController;
use Ruth\RpcBundle\Http\JsonRpc;
use Ruth\RpcBundle\Http\HttpResponse\JsonRpcResponse;
use Ruth\RpcBundle\Service\ServiceTest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonRpcControllerTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    /** @var VictoriousPuppyController */
    protected $controller;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $abstract;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $mockController;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $loggerMock;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $container  = self::$kernel->getContainer();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $this->mockController = $this->getMockBuilder(VictoriousPuppyController::class)
                               ->setMethods([
                                   'index', 
                                   'execute',
                                   'callService',
                               ]) 
                               ->disableOriginalConstructor()
                               ->getMock();                           
   
        $this->abstract = $this->getMockForAbstractClass(
            JsonRpcController::class,
            [$this->loggerMock]
        );

        $this->controller = $container->get(VictoriousPuppyController::class);                    
    }

    public function testInstanceOfAbstractJsonRpcController()
    {
        $this->assertInstanceOf(
            JsonRpcController::class, 
            $this->abstract
        );
    }

    public function testInstanceOfController()
    {
        $this->assertInstanceOf(
            VictoriousPuppyController::class, 
            $this->controller
        );
    }

    public function testSuccessExecute()
    {
        $jsonRpc = new JsonRpc(
            "ruth_rpc.service_test:foo",
            ["x"=>1,"y"=>2],
            "2957f28d-8797-42b1-bd5d-45834b3202d"
        );

        // Let's create a request
        $request = Request::create(
            '/test',
            'POST',
            [],
            [],
            [],
            [],
            json_encode($jsonRpc)
        );

        $result = $this->controller->execute($request);

        $this->assertInstanceOf(
            JsonRpcResponse::class,
            $result
        );
        $this->assertEquals(
            '{"jsonrpc":"2.0","result":2,"id":"2957f28d-8797-42b1-bd5d-45834b3202d"}', 
            $result->getContent()
        );
    }

    public function testSuccessNotificationWrongIdKeyProvided()
    {
        $data = '{"jsonrpc":"2.0","method":"ruth_rpc.service_test:foo","ideal":"2957f28d-8797-42b1-bd5d-45834b3202d","params":{"x":1,"y":2}}';

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

        $result = $this->controller->execute($request);

        $this->assertInstanceOf(
            JsonRpcResponse::class,
            $result
        );

        $this->assertEquals(
            '',
            $result->getContent()
        );
    }

    public function testSuccessNotificationWithNullId()
    {
        $data = '{"jsonrpc":"2.0","method":"ruth_rpc.service_test:foo","params":{"x":1,"y":2}}';

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

        $result = $this->controller->execute($request);

        $this->assertInstanceOf(
            JsonRpcResponse::class,
            $result
        );

        $this->assertEquals(
            '',
            $result->getContent()
        );
    }

    public function testSuccessWithNullParamsOnServiceMethodWithNoArguments()
    {
        $data = '{"jsonrpc":"2.0","method":"ruth_rpc.service_test:boo","id":"2957f28d-8797-42b1-bd5d-45834b3202d"}';

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

        $result = $this->controller->execute($request);

        $this->assertInstanceOf(
            JsonRpcResponse::class,
            $result
        );

        $this->assertEquals(
            '{"jsonrpc":"2.0","result":1,"id":"2957f28d-8797-42b1-bd5d-45834b3202d"}',
            $result->getContent()
        );
    }

    public function testSuccessBatch()
    {
        $jsonRpc1 = new JsonRpc(
            "ruth_rpc.service_test:sleep",
            [1],
            "2957f28d-8797-42b1-bd5d-45834b3202d"
        );

        $jsonRpc2 = new JsonRpc(
            "ruth_rpc.service_test:sleep",
            [1],
            "2957f28d-8797-42b1-bd5d-45834b3202d"
        );

        $jsonRpc3 = new JsonRpc(
            "ruth_rpc.service_test:boo",
            [],
            "2957f28d-8797-42b1-bd5d-45834b3202d"
        );

        // Let's create a request
        $request = Request::create(
            '/test',
            'POST',
            [],
            [],
            [],
            [],
            json_encode([$jsonRpc1, $jsonRpc2, $jsonRpc3])
        );

        $result = $this->controller->execute($request);

        $this->assertInstanceOf(
            JsonResponse::class,
            $result
        );

        $this->assertEquals(
            '[{"jsonrpc":"2.0","result":1,"id":"2957f28d-8797-42b1-bd5d-45834b3202d"}' .
                     ',{"jsonrpc":"2.0","result":1,"id":"2957f28d-8797-42b1-bd5d-45834b3202d"}' .
                     ',{"jsonrpc":"2.0","result":1,"id":"2957f28d-8797-42b1-bd5d-45834b3202d"}]',
            $result->getContent()
        );
    }

    public function testSuccessBatchYield()
    {
        $jsonRpc1 = new JsonRpc(
            "ruth_rpc.service_test:sleep",
            [1],
            "2957f28d-8797-42b1-bd5d-45834b3202d"
        );

        $jsonRpc2 = new JsonRpc(
            "ruth_rpc.service_test:sleep",
            [1],
            "2957f28d-8797-42b1-bd5d-45834b3202d"
        );

        $jsonRpc3 = new JsonRpc(
            "ruth_rpc.service_test:boo",
            [],
            "2957f28d-8797-42b1-bd5d-45834b3202d"
        );

        // Let's create a request
        $request = Request::create(
            '/test',
            'POST',
            [],
            [],
            [],
            [],
            json_encode([$jsonRpc1, $jsonRpc2, $jsonRpc3])
        );

        $result = $this->controller->setYieldBatch(true)->execute($request);

        $this->assertInstanceOf(
            JsonResponse::class,
            $result
        );

        $this->assertEquals(
            '[{"jsonrpc":"2.0","result":1,"id":"2957f28d-8797-42b1-bd5d-45834b3202d"}' .
                     ',{"jsonrpc":"2.0","result":1,"id":"2957f28d-8797-42b1-bd5d-45834b3202d"}' .
                     ',{"jsonrpc":"2.0","result":1,"id":"2957f28d-8797-42b1-bd5d-45834b3202d"}]',
            $result->getContent()
        );
    }

    public function testFailParseErrorEmptyJsonRpcVersion()
    {
        // Let's create a request
        $request = Request::create(
            '/test',
            'POST',
            [],
            [],
            [],
            [],
            '{"method":"ruth_rpc.service_test:foo","params":{"x":1,"y":2},"id":"2957f28d-8797-42b1-bd5d-45834b3202d"}'
        );

        $result = $this->controller->execute($request);

        $this->assertInstanceOf(
            JsonRpcResponseError::class,
            $result
        );

        $this->assertEquals(
            '{"jsonrpc":"2.0","error":{"code":"-32700","message":"Parse error","data":"Invalid Request"},"id":null}',
            $result->getContent()
        );
    }

    public function testFailParseErrorJsonRpcVersion()
    {
        // Let's create a request
        $request = Request::create(
            '/test',
            'POST',
            [],
            [],
            [],
            [],
            '{"jsonrpc":"1.0","method":"ruth_rpc.service_test:foo","params":{"x":1,"y":2},"id":"2957f28d-8797-42b1-bd5d-45834b3202d"}'
        );

        $result = $this->controller->execute($request);

        $this->assertInstanceOf(
            JsonRpcResponseError::class,
            $result
        );

        $this->assertEquals(
            '{"jsonrpc":"2.0","error":{"code":"-32700","message":"Parse error","data":"Invalid Request: json-rpc version is not 2.0"},"id":null}',
            $result->getContent()
        );
    }

    public function testFailVerbIsNotPOST()
    {
        // Let's create a request
        $request = Request::create(
            '/test',
            'GET',
            [],
            [],
            [],
            [],
            '{"jsonrpc":"2.0","method":"ruth_rpc.service_test:foo","id":"2957f28d-8797-42b1-bd5d-45834b3202d","params":{"x":1,"y":2}}'
        );

        $result = $this->controller->execute($request);

        $this->assertInstanceOf(
            JsonRpcResponseError::class,
            $result
        );

        $this->assertEquals(
            '{"jsonrpc":"2.0","error":{"code":"-32700","message":"Parse error","data":"Invalid Request: verb should be POST"},"id":null}',
            $result->getContent()
        );
    }

    public function testFailParseErrorNoMethodKeyProvided()
    {
        $data = '{"jsonrpc":"2.0","no-method":"ruth_rpc.service_test:foo","id":"2957f28d-8797-42b1-bd5d-45834b3202d","params":{"x":1,"y":2}}';

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

        $result = $this->controller->execute($request);

        $this->assertInstanceOf(
            JsonRpcResponseError::class,
            $result
        );

        $this->assertEquals(
            '{"jsonrpc":"2.0","error":{"code":"-32700","message":"Parse error","data":"Invalid Request"},"id":null}',
            $result->getContent()
        );
    }

    public function testFailParseErrorWrongJsonRpcKeyProvided()
    {
        $data = '{"json-rpc":"2.0","method":"ruth_rpc.service_test:foo","id":"2957f28d-8797-42b1-bd5d-45834b3202d","params":{"x":1,"y":2}}';

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

        $result = $this->controller->execute($request);

        $this->assertInstanceOf(
            JsonRpcResponseError::class,
            $result
        );

        $this->assertEquals(
            '{"jsonrpc":"2.0","error":{"code":"-32700","message":"Parse error","data":"Invalid Request"},"id":null}',
            $result->getContent()
        );
    }

    public function testFailServiceNotFound()
    {
        $jsonRpc = new JsonRpc(
            "ruth_rpc.unknown:foo",
            ["x"=>1,"y"=>2],
            "2957f28d-8797-42b1-bd5d-45834b3202d"
        );

        // Let's create a request
        $request = Request::create(
            '/test',
            'POST',
            [],
            [],
            [],
            [],
            json_encode($jsonRpc)
        );

        $result = $this->controller->execute($request);

        $this->assertInstanceOf(
            JsonRpcResponseError::class,
            $result
        );

        $this->assertEquals(
            '{"jsonrpc":"2.0","error":{"code":"-32601","message":"Method not found"},"id":null}',
            $result->getContent()
        );
    }

    public function testFailWithNullParamsOnServiceMethodExpectingParams()
    {
        $data = '{"jsonrpc":"2.0","method":"ruth_rpc.service_test:foo","id":"2957f28d-8797-42b1-bd5d-45834b3202d"}';

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

        $result = $this->controller->execute($request);

        $this->assertInstanceOf(
            JsonRpcResponseError::class,
            $result
        );

        $this->assertEquals(
            '{"jsonrpc":"2.0","error":{"code":"-32603","message":"Internal error","data":"Unable to invoke the callable because no value was given for parameter 1 ($x)"},"id":null}',
            $result->getContent()
        );
    }

    public function testFailServiceMethodFound()
    {
        $data = '{"jsonrpc":"2.0","method":"ruth_rpc.service_test:booooo","id":"2957f28d-8797-42b1-bd5d-45834b3202d"}';

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

        $result = $this->controller->execute($request);

        $this->assertInstanceOf(
            JsonRpcResponseError::class,
            $result
        );

        $this->assertEquals(
            '{"jsonrpc":"2.0","error":{"code":"-32601","message":"Method not found"},"id":null}',
            $result->getContent()
        );
    }

    public function testInstanceOfAbstractJsonRpcControllerMockSuccess()
    {
        $jsonRpc = new JsonRpc(
            "ruth_rpc.service_test:foo",
            ["x"=>1, "y"=>2],
            "2957f28d-8797-42b1-bd5d-45834b3202d"
        );

        $request = Request::create(
            '/test',
            'POST',
            [],
            [],
            [],
            [],
            json_encode($jsonRpc)
        );

        /** @var Mock */
        $mockService = $this->getMockBuilder(ServiceTest::class)
                            ->setMethods(['foo']) 
                            ->disableOriginalConstructor()
                            ->getMock();  
                            
        $mockService->expects($this->once())
                    ->method('foo')
                    ->with(1,2)
                    ->will($this->returnValue(2));

        $mockService->foo(1,2);

        $this->mockController
             ->expects($this->once())
             ->method('index')
             ->with($request)
             ->willReturn(new JsonRpcResponse($jsonRpc->getId(), 2));

        $this->mockController
             ->expects($this->once())
             ->method('execute')
             ->with($request)
             ->willReturn(new JsonRpcResponse($jsonRpc->getId(), 2));
        
        $this->mockController->index($request); 
        $this->mockController->execute($request); 
    }

    

}