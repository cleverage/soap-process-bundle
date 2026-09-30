RequestTask
===========

Call a method of a SOAP service, through a registered [client](../client.md), and output the result.

Task reference
--------------

* **Service**: `CleverAge\SoapProcessBundle\Task\RequestTask`

Accepted inputs
---------------

`array`: list of the arguments passed as `$args` to
[SoapClient::__soapCall()](https://www.php.net/manual/en/soapclient.soapcall.php). An empty input (`null`, `[]`, …)
calls the method without argument.

For a document/literal service, the arguments of the method are usually wrapped in a single array, e.g.
`{ parameters: { sCountryISOCode: FR } }` (in WSDL mode, the keys of the first level are ignored, only the order
matters).

Possible outputs
----------------

`mixed`: the result of the SOAP call, usually a `stdClass` (or an array of `stdClass`) built by `SoapClient` from the
response.

When the call fails, an exception is thrown and the `error_strategy` applies, see Notes.

Options
-------

| Code                | Type          | Required | Default | Description                                                                                                                                                              |
|---------------------|---------------|:--------:|---------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `client`            | `string`      |  **X**   |         | Code of the [client](../client.md) to use                                                                                                                                |
| `method`            | `string`      |  **X**   |         | Name of the SOAP method to call                                                                                                                                          |
| `soap_call_options` | `array\|null` |          | `null`  | `$options` of [SoapClient::__soapCall()](https://www.php.net/manual/en/soapclient.soapcall.php): `location`, `uri`, `soapaction`                                          |
| `soap_call_headers` | `array\|null` |          | `null`  | Headers sent with the request, as a list of `header name => header options` (see below), converted to [SoapHeader](https://www.php.net/manual/en/class.soapheader.php) |

Each header of `soap_call_headers` has the following options:

| Code        | Type     | Required | Default | Description                                                                        |
|-------------|----------|:--------:|---------|------------------------------------------------------------------------------------|
| `namespace` | `string` |  **X**   |         | Namespace of the SOAP header element                                               |
| `data`      | `mixed`  |  **X**   |         | Content of the SOAP header: a scalar, an array (converted to a structure by PHP) … |

Examples
--------

* Call a method without argument

```yaml
# Task configuration level
list_countries:
  service: '@CleverAge\SoapProcessBundle\Task\RequestTask'
  options:
    client: country_info
    method: FullCountryInfoAllCountries
  outputs: [transform]
```

* Call a method with the arguments built by a previous task, sending an authentication header and forcing the
  endpoint URL

```yaml
# Task configuration level
get_order:
  service: '@CleverAge\SoapProcessBundle\Task\RequestTask'
  error_strategy: skip
  options:
    client: legacy_erp
    method: GetOrder
    soap_call_options:
      location: 'https://erp.example.com/soap/orders'
    soap_call_headers:
      AuthHeader: # Name of the header element
        namespace: 'urn:erp'
        data:
          Username: '%env(ERP_SOAP_LOGIN)%'
          Token: '%env(ERP_SOAP_TOKEN)%'
  outputs: [transform]
  error_outputs: [log_error]
```

Notes
-----

* When the call fails (the client returns `false`, see [client](../client.md)), the task logs an error
  `Empty resultset for query`, with the options and the last request and response in the log context, then throws a
  `RuntimeException` (`Soap call '<method>' on client '<client>' failed`), handled by the `error_strategy`:
  - with `error_strategy: skip`, the `outputs` tasks are skipped and the `error_outputs` tasks receive the input of
    the task
  - with `error_strategy: stop`, the process fails and the console command returns an error code
* An exception thrown by the client (e.g. a `SoapFault` when the WSDL cannot be loaded, or a
  `MissingClientException` for an unknown `client`) is handled as usual by the `error_strategy`.
* `soap_call_options` and `soap_call_headers` are set on the client each time the task is executed, including when
  they are `null`: they overwrite the values configured with `setSoapOptions` / `setSoapHeaders` in the client
  service definition.
