<?php

namespace Ruth\RpcBundle\Tests;

use Ruth\RpcBundle\Http\HttpResponse\JsonRpcResponse;
use Ruth\RpcBundle\Service\ServiceMediator;
use Symfony\Component\DependencyInjection\Container;

class JsonRpcResponseTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    protected $containerMock;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testGoodResponse()
    {
        $response = new JsonRpcResponse(
            "2957f28d-8797-42b1-bd5d-45834b3202d",
            ['x'=>1,'y'=>2]
        );

        $content = $response->getContent();

        $this->assertEquals(
            '{"jsonrpc":"2.0","result":{"x":1,"y":2},"id":"2957f28d-8797-42b1-bd5d-45834b3202d"}',
            $content
        );

        $this->assertEquals($response->getStatusCode(), 200);
    }

    public function testGoodResponseOnNotification()
    {
        $response = new JsonRpcResponse(
            null,
            ['x'=>1,'y'=>2]
        );

        $content = $response->getContent();

        $this->assertEquals('', $content);
        $this->assertEquals($response->getStatusCode(), 200);
    }
}