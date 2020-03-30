Getting Started With RuthRpcBundle
==================================

This bundle allows you to implement any controller you want
implementing json-rpc version 2.0 `see <https://www.jsonrpc.org/specification/>`.

Installation
------------

**As application bundle**

.. code-block:: bash

    composer require ruth/json-rpc-bundle

**Stand Alone**

    $ git clone https://github.com/filipefernandes007/ruth-json-rpc-bundle

    $ cd ruth-json-rpc-bundle

    $ composer self-update

    $ composer install

Usage
-----

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
            // Just invoke execute method to handle the procedure call.
            // You can return it, or deal with the result to send to another service.
            // Avoid polute the endpoint, since the resource it is the procedure/service itself.
            return $this->execute($request); // just call execute
        }

        /**
         * @param Request $request
         */
        protected function logRequest(Request $request) : void
        {
            // You can do more than just log, by overriding logRequest,
            // like sending data to elastic or Scalyr, or do nothing!
            parent::logRequest($request);
        }

        /**
         * @param Request $request
         * @param array|null $result
         */
        protected function logResponse(Request $request, ?array $result): void
        {
            // You can do more than just log, by overriding logResponse,
            // like sending data to elastic or Scalyr, or do nothing!
            parent::logResponse($request, $result);
        }
    }

Curl
----

You should call like this (just an example):

.. code-block:: bash

    curl -X POST http://localhost:8083/rpc -d '{"jsonrpc":"2.0","method":"ruth_rpc.service_test:foo","id":"2957f28d-8797-42b1-bd5d-45834b3202d","params":{"x":1,"y":2}}'

Every request should have specification mandatory fields.
To specify service method you should place in *method* field as follows:

<service alias>:<method name>.

Service and method should be public.

Prerequisites
-------------

This version of the bundle requires Symfony 4.0+ and PHP >= 7.3.