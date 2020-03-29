Getting Started With RuthRpcBundle
==================================

This bundle allows you to implement any controller you want
implementing json-rpc version 2.0.

You only have to *extend* your controller from **Ruth\RpcBundle\Controller\JsonRpcController**.

Then, in your action, just call **$this->execute($request)**, believing
that **$request** is your *Request* instance.

Do something like this:

.. code-block:: php

    ...
    use Ruth\RpcBundle\Controller\JsonRpcController;
    ...

    class VictoriousPuppyController extends JsonRpcController
    {
        /** @Route("/rpc", methods={"POST"}, name="victorious_puppy_rpc") */
        public function index(Request $request) : Response
        {
            return $this->execute($request); // just call execute
        }
    }

Usage
-----

You should call like this:

.. code-block:: bash

    curl -X POST http://localhost:8083/en/delicious-elephant -d '{"jsonrpc":"2.0","method":"ruth_rpc.service_test:foo","id":"2957f28d-8797-42b1-bd5d-45834b3202d","params":{"x":1,"y":2}}'

Prerequisites
-------------

This version of the bundle requires Symfony 4.0+ and PHP >= 7.3.