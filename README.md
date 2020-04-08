RuthRpcBundle
===================

[![Latest Stable Version](https://poser.pugx.org/ruth/json-rpc-bundle/v/stable)](https://packagist.org/packages/ruth/json-rpc-bundle)
[![Total Downloads](https://poser.pugx.org/ruth/json-rpc-bundle/downloads)](https://packagist.org/packages/ruth/json-rpc-bundle)
[![Latest Unstable Version](https://poser.pugx.org/ruth/json-rpc-bundle/v/unstable)](https://packagist.org/packages/ruth/json-rpc-bundle)
[![License](https://poser.pugx.org/ruth/json-rpc-bundle/license)](https://packagist.org/packages/ruth/json-rpc-bundle)
[![composer.lock](https://poser.pugx.org/ruth/json-rpc-bundle/composerlock)](https://packagist.org/packages/ruth/json-rpc-bundle)

About
-----

This bundle is an implementation of JSON-RPC 2.0 Specification [here](https://www.jsonrpc.org/specification).

It's open for other types of implementations, but for now, it's strict to that.

Documentation
-------------

The source of the documentation is stored in the `Resources/doc/` folder
in this bundle:

[Read the Documentation](Resources/doc/index.rst)

Installation 
------------

#### As Application Bundle
```bash
$ composer require ruth/json-rpc-bundle
```

#### Stand Alone
```bash
$ git clone https://github.com/filipefernandes007/ruth-json-rpc-bundle
$ cd ruth-json-rpc-bundle
$ composer self-update
$ composer install
```

License
-------

This bundle is under the MIT license. See the complete license [in the bundle](LICENSE)

About
-----

RuthRpcBundle is a [Filipe Fernandes](https://github.com/filipefernandes007/ruth-json-rpc-bundle) initiative.

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/filipefernandes007/ruth-json-rpc-bundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.