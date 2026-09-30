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

`false` when the call failed (`SoapFault`), see Notes.

Options
-------

| Code     | Type     | Required | Default | Description                               |
|----------|----------|:--------:|---------|-------------------------------------------|
| `client` | `string` |  **X**   |         | Code of the [client](../client.md) to use |
| `method` | `string` |  **X**   |         | Name of the SOAP method to call           |

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

* The transformer does not handle any SOAP call option or header: the values currently set on the client are used
  (from the `calls` of the client service definition, or from the last [RequestTask](../tasks/request_task.md)
  executed with the same client).
* A failed call is logged by the [client](../client.md) and returns `false`, without any exception: check the result
  (or chain a transformer that fails on `false`, like `property_accessor` above) if the process must not go on
  silently with a `false` value.
* One SOAP call is made each time the transformer is applied: to avoid calling the service several times with the same
  arguments, wrap it in the
  [cached](https://github.com/cleverage/process-bundle/blob/main/docs/reference/transformers/cached_transformer.md)
  transformer.
