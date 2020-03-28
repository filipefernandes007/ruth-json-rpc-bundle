<?php

namespace Ruth\RpcBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * You cannot call directly from AbstractController the service from app container.        
 * This helps to get a service in case you are using a class that extends AbstractController.
 */
class ServiceMediator {
    /** @var $container ContainerInterface */
    private $container;

    
    public function __construct(ContainerInterface $container) 
    {
        $this->container = $container;
    }

    public function getService(string $service) {
        return $this->container->get($service);
    }
}
