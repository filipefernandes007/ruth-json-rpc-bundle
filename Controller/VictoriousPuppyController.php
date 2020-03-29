<?php

namespace Ruth\RpcBundle\Controller;

use Ruth\RpcBundle\Controller\JsonRpcController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VictoriousPuppyController extends JsonRpcController
{
    /**
     * @Route("/victorious-puppy", methods={"POST"}, name="victorious_puppy")
     */
    public function index(Request $request) : Response
    {
        // Just invoke execute method to handle the procedure call.
        // You can return it, or deal with the result to send to another service.
        // Avoid polute the endpoint, since the resource it is the procedure/service itself. 
        return $this->execute($request);            
    }

    /**
     * @param Request $request
     */
    protected function logRequest(Request $request) : void
    {
        // You can do more than just log, by overriding logRequest,
        // like sending data to elastic or scalyr, or do nothing!
        parent::logRequest($request);
    }

    /**
     * @param Request $request
     * @param array|null $result
     */
    protected function logResponse(Request $request, ?array $result): void
    {
        // You can do more than just log, by overriding logRequest,
        // like sending data to elastic or scalyr, or do nothing!
        parent::logResponse($request, $result);
    }
}
