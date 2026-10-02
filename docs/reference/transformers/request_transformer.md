RequestTransformer
==================

Call a method of a SOAP service, through a registered [client](../client.md), with the input value as arguments, and
return the result. Unlike the [RequestTask](../tasks/request_task.md), it can be used anywhere a transformer is
accepted, e.g. to enrich a single property inside a `mapping`.

Transformer reference
---------------------

* **Service**: `CleverAge\SoapProcessBundle\Transformer\RequestTransformer`
* **Transformer code**: `soap_request`

Accepted inputs
---------------

`array`: list of the arguments passed as `$args` to
[SoapClient::__soapCall()](https://www.php.net/manual/en/soapclient.soapcall.php) (see the
[RequestTask](../tasks/request_task.md#accepted-inputs)). Any other type throws an `\UnexpectedValueException`
(`Expecting an array of value`).

Possible outputs
----------------

`mixed`: the result of the SOAP call, usually a `stdClass` built by `SoapClient` from the response.

A failed call (`SoapFault`) throws a `RuntimeException`, see Notes.

Options
-------

| Code                | Type          | Required | Default | Description                                                                                          |
|---------------------|---------------|:--------:|---------|------------------------------------------------------------------------------------------------------|
| `client`            | `string`      |  **X**   |         | Code of the [client](../client.md) to use                                                            |
| `method`            | `string`      |  **X**   |         | Name of the SOAP method to call                                                                      |
| `soap_call_options` | `array\|null` |          | `null`  | `$options` of `SoapClient::__soapCall()`, as for the [RequestTask](../tasks/request_task.md#options) |
| `soap_call_headers` | `array\|null` |          | `null`  | Headers sent with the request, as for the [RequestTask](../tasks/request_task.md#options)            |

Examples
--------

* Replace an ISO country code by the country name
  - input: `FR`
  - the two [wrapper](https://github.com/cleverage/process-bundle/blob/main/docs/reference/transformers/wrapper_transformer.md)
    transformers build the arguments `{ parameters: { sCountryISOCode: FR } }`
  - the [property_accessor](https://github.com/cleverage/process-bundle/blob/main/docs/reference/transformers/property_accessor_transformer.md)
    transformer extracts the value from the `stdClass` response
  - output: `France`

```yaml
# Transformer options level
wrapper:
  wrapper_key: sCountryISOCode
wrapper#2:
  wrapper_key: parameters
soap_request:
  client: country_info
  method: CountryName
property_accessor:
  property_path: CountryNameResult
```

Notes
-----

* `soap_call_options` and `soap_call_headers` are set on the client for the call only, then the previous values are
  restored: when they are not set, the values of the client service definition (`calls`) are used.
* A failed call (`SoapFault`) is logged by the [client](../client.md), then the transformer throws a
  `RuntimeException` (`Soap call '<method>' on client '<client>' failed`). A method returning `false` returns
  `false`.
* One SOAP call is made each time the transformer is applied: to avoid calling the service several times with the same
  arguments, wrap it in the
  [cached](https://github.com/cleverage/process-bundle/blob/main/docs/reference/transformers/cached_transformer.md)
  transformer.
