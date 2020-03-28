Getting Started With RuthRpcBundle
==================================

This bundle allows you to implement any controller you want
implementing json rpc version 2.0.

You only have to 'extend' your controller from Ruth\RpcBundle\Controller\JsonRpcController.

Then, in your action, just call `$this->execute($request)`, believing
that `$request` is your `Request` instance.

Do something like this:

.. code-block:: php

class VictoriousPuppyController extends JsonRpcController
{
    /**
     * @Route("/rpc", methods={"POST"}, name="victorious_puppy_rpc")
     */
    public function index(Request $request) : Response
    {
        // Just invoke execute method to handle the procedure call.
        // You can return it, or deal with the result to send to another service.
        // Avoid polute the endpoint, since the resource it is the procedure/service itself.
        return $this->execute($request);
    }


Prerequisites
-------------

This version of the bundle requires Symfony 4.0+.
