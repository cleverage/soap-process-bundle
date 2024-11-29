RequestTask
===============

Call a SOAP Request and get result.

Task reference
--------------

* **Client Service Interface**: `CleverAge\SoapProcessBundle\Client\ClientInterface`
* **Task Service**: `CleverAge\SoapProcessBundle\Task\RequestTask`

Accepted inputs
---------------

`array`: list of of the arguments to pass as `$args` to the [SoapClient::__soapCall()](https://www.php.net/manual/en/soapclient.soapcall.php) method.

Possible outputs
----------------

`false|stdClass|array`: the result of the soap call.

Options
-------

### For Client

| Code            | Type             | Required  | Default | Description                                                                   |
|-----------------|------------------|:---------:|---------|-------------------------------------------------------------------------------|
| `code`          | `string`         |   **X**   |         | Service identifier, used by Task client option                                |
| `wsdl`          | `string or null` |           |         | URI of a WSDL file describing the service                                     |
| `options`       | `array`          |           | []      | An associative array specifying additional options for the SOAP client.       |
| `options.trace` | `boolean`        |           | true    | Captures request and response information. Add debug informations into logger |

Calls setter methods in `CleverAge\SoapProcessBundle\Client\ClientInterface` to add more options.

### For Task

| Code                          | Type                                          |              Required                    | Default | Description                                                         |
|-------------------------------|-----------------------------------------------|:----------------------------------------:|---------|---------------------------------------------------------------------|
| `client`                      | `string`                                      |                  **X**                   |         | `ClientInterface` service identifier                                |
| `method`                      | `string`                                      |                  **X**                   |         | The name of the SOAP function to call.                              |
| `soap_call_options`           | `array or null`                               |                                          | null    | An associative array of options to pass to the client.              |
| `soap_call_headers`           | `array or null` resolved as \SoapHeader array |                                          | null    | An array of headers to be sent along with the SOAP request.         |
| `soap_call_headers.namespace` | `array or null`                               | **X** if `soap_call_headers` is not null |         | The namespace of the SOAP header element.                           |
| `soap_call_headers.data`      | `array or null`                               | **X** if `soap_call_headers` is not null |         | A SOAP header's content. It can be a PHP value or a SoapVar object. |

Examples
--------

### Client

```yaml
services:
  app.cleverage_soap_process.client.domain_sample:
    class: CleverAge\SoapProcessBundle\Client\Client
    bind:
      $code: 'domain_sample'
      $wsdl: 'https://domain/sample.wsdl'
      $options:
        trace: true
        exceptions: true
    calls:
      - [ setSoapOptions, [ { features: SOAP_SINGLE_ELEMENT_ARRAYS} ] ]
    tags:
      - { name: cleverage.soap.client }
```  

### Task

```yaml
# Task configuration level
code:
  service: '@CleverAge\SoapProcessBundle\Task\RequestTask'
  options:
    client: domain_sample
    method: 'MethodToCall'
```
