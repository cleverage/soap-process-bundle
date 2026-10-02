Client
======

A SOAP client wraps a PHP [SoapClient](https://www.php.net/manual/en/class.soapclient.php) (a WSDL and its options)
and makes it available to the [RequestTask](tasks/request_task.md) and the
[RequestTransformer](transformers/request_transformer.md) under a short **code**, referenced by their `client` option.

Client reference
----------------

* **Interface**: `CleverAge\SoapProcessBundle\Client\ClientInterface`
* **Base class**: `CleverAge\SoapProcessBundle\Client\Client`
* **Service tag**: `cleverage.soap.client`, every tagged service is registered in the
  `cleverage_soap_process.registry.client` registry (`CleverAge\SoapProcessBundle\Registry\ClientRegistry`)

The bundle does not declare any client: you have to register at least one service, either with the
`CleverAge\SoapProcessBundle\Client\Client` class or with your own implementation of `ClientInterface`.

Constructor arguments
---------------------

Arguments of the `CleverAge\SoapProcessBundle\Client\Client` base class:

| Code       | Type                       | Required | Default | Description                                                                                                                                  |
|------------|----------------------------|:--------:|---------|----------------------------------------------------------------------------------------------------------------------------------------------|
| `$logger`  | `Psr\Log\LoggerInterface`  |  **X**   |         | Logger used to trace the calls (see Notes)                                                                                                   |
| `$code`    | `string`                   |  **X**   |         | Unique client code, referenced by the `client` option of the task and the transformer. It cannot be empty or `'0'`                          |
| `$wsdl`    | `string\|null`             |  **X**   |         | URI of the WSDL file, or `null` to work in non-WSDL mode (the `location` and `uri` options are then required by `SoapClient`)                |
| `$options` | `array`                    |          | `[]`    | Options of the [SoapClient constructor](https://www.php.net/manual/en/soapclient.construct.php) (`exceptions`, `features`, `login`, …)       |

Setters
-------

These values can be set with `calls` in the service definition. Note that the [RequestTask](tasks/request_task.md)
overwrites the SOAP call options and headers each time it is executed (see Notes).

| Method                                  | Description                                                                                                                                                            |
|-----------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `setWsdl(?string $wsdl)`                | Change the WSDL URI (only before the first call: the `SoapClient` is created once)                                                                                    |
| `setOptions(array $options)`            | Change the `SoapClient` constructor options (only before the first call)                                                                                              |
| `setSoapOptions(?array $options)`       | Options of [SoapClient::__soapCall()](https://www.php.net/manual/en/soapclient.soapcall.php): `location`, `uri`, `soapaction`                                         |
| `setSoapHeaders(?array $headers)`       | List of `\SoapHeader` sent with each call                                                                                                                              |
| `setSoapClient(\SoapClient $client)`    | Inject an already built `SoapClient` (e.g. a subclass), instead of letting the client build it from the WSDL and the options                                          |

Examples
--------

* Client of the public [CountryInfoService](http://webservices.oorsprong.org/websamples.countryinfo/CountryInfoService.wso)
  (used by the [cookbooks](../index.md#cookbooks))
  - `features` is a `SoapClient` constructor option, so it is set in `$options`. The `!php/const` YAML tag is needed
    to get the value of the PHP constant (a plain `SOAP_SINGLE_ELEMENT_ARRAYS` would be a string)
  - `SOAP_SINGLE_ELEMENT_ARRAYS` returns an array even when a list contains a single element

```yaml
# config/services.yaml
services:
    app.cleverage_soap_process.client.country_info:
        class: CleverAge\SoapProcessBundle\Client\Client
        arguments:
            $logger: '@logger'
            $code: 'country_info'
            $wsdl: 'http://webservices.oorsprong.org/websamples.countryinfo/CountryInfoService.wso?WSDL'
            $options:
                trace: true
                exceptions: true
                cache_wsdl: !php/const WSDL_CACHE_BOTH
                features: !php/const SOAP_SINGLE_ELEMENT_ARRAYS
        tags:
            - { name: cleverage.soap.client }
```

With `autowire: true` in the `_defaults` of your `config/services.yaml`, the `$logger` argument can be omitted.

* Client in non-WSDL mode, with HTTP basic authentication

```yaml
# config/services.yaml
services:
    app.cleverage_soap_process.client.legacy_erp:
        class: CleverAge\SoapProcessBundle\Client\Client
        arguments:
            $logger: '@logger'
            $code: 'legacy_erp'
            $wsdl: ~
            $options:
                location: '%env(ERP_SOAP_LOCATION)%'
                uri: 'urn:erp'
                login: '%env(ERP_SOAP_LOGIN)%'
                password: '%env(ERP_SOAP_PASSWORD)%'
        tags:
            - { name: cleverage.soap.client }
```

* Custom client, overriding the call of one SOAP method
  - `Client::call()` looks for a `soapCall<Method>` method (`ucfirst()` of the method name) and uses it instead of
    the generic call, which allows to prepare the arguments or post-process the result of one method

```php
<?php

declare(strict_types=1);

namespace App\Soap;

use CleverAge\SoapProcessBundle\Client\Client;

class CountryInfoClient extends Client
{
    /**
     * Called instead of the generic call for the "CountryName" method.
     *
     * @param array<mixed> $input
     */
    protected function soapCallCountryName(array $input): mixed
    {
        // Throws the SoapFault when the call fails
        return $this->doSoapCall('CountryName', $input)->CountryNameResult;
    }
}
```

Notes
-----

* Codes must be unique: registering two clients with the same code throws an `\UnexpectedValueException` giving the
  ids of both services (`Client <code> is already defined by service "<id>", cannot register service "<id>"`) when
  the registry is instantiated, i.e. when the first SOAP task or
  transformer service is built.
* Using a code that is not registered throws a `CleverAge\SoapProcessBundle\Exception\MissingClientException`
  (`No Soap client with code : <code>`).
* The `SoapClient` is created lazily, on the first call, and then reused: the WSDL is only loaded once per client.
  A WSDL that cannot be loaded throws a `SoapFault` at this moment, which is not caught by the client.
* The `trace` option is always forced to `true` when the `SoapClient` is created, so that the last request and
  response are always available (they are added to the log context of a failed call). Setting `trace: true` in
  `$options` additionally logs them at `debug` level after each successful call.
* Each call is logged at `notice` level (`Soap call '<method>' on '<wsdl>'`), including the ones handled by a
  `soapCall<Method>()` override. When the call fails with a `SoapFault`, the error is logged at `alert` level, with the
  last request and response in the log context, then the `SoapFault` is thrown. With the `exceptions: false` option,
  `SoapClient` returns the `SoapFault` instead of throwing it: it is handled the same way.
* A client service is shared by default: the SOAP options and headers set with `setSoapOptions()` /
  `setSoapHeaders()` (e.g. in the `calls` of the service definition) are used by every call. The
  [RequestTask](tasks/request_task.md) and the [RequestTransformer](transformers/request_transformer.md) set their
  own `soap_call_options` / `soap_call_headers` for their call only, then restore these values.
