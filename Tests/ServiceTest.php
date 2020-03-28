<?php

namespace Ruth\RpcBundle\Tests;

use Ruth\RpcBundle\Service\ServiceMediator;
use Symfony\Component\DependencyInjection\Container;

class ServiceTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    protected $containerMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->setDefaultMocks();
    }

    protected function setDefaultMocks()
    {
        $this->containerMock = $this->getMockBuilder(Container::class)->getMock();
    }

    public function testServiceMediator() 
    {
        $service = new ServiceMediator($this->containerMock);

        $class = $service->getService(ServiceMediator::class);
        
        $this->assertNotInstanceOf(ServiceMediator::class, $class);
    }

    public function testServiceMediatorBootKernel() 
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();
        $service = $container->get(ServiceMediator::class);
        
        $this->assertEquals(ServiceMediator::class, get_class($service));
    }
}