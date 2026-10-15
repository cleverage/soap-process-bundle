## Prerequisite

CleverAge/ProcessBundle must be [installed](https://github.com/cleverage/process-bundle/blob/main/docs/01-quick_start.md#installation).
The PHP [soap](https://www.php.net/manual/en/book.soap.php) extension is required.

## Installation

Make sure Composer is installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Open a command console, enter your project directory and install it using composer:

```bash
composer require cleverage/soap-process-bundle
```

Remember to add the following line to config/bundles.php (not required if Symfony Flex is used)

```php
CleverAge\SoapProcessBundle\CleverAgeSoapProcessBundle::class => ['all' => true],
```

## Configuration

The bundle has no configuration. Declare at least one SOAP client as a service tagged `cleverage.soap.client` with its
`code` (or with the `#[AsSoapClient]` attribute), which is then used by the `client` option of the task and the
transformer (see [Client](reference/client.md)):

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
                exceptions: true
                features: !php/const SOAP_SINGLE_ELEMENT_ARRAYS
        tags:
            - { name: cleverage.soap.client, code: country_info }
```

The task is registered as a service (non-shared) and is used in your processes by its code, `soap_request` (e.g.
`task: soap_request`, requires `cleverage/process-bundle` 5.4 or later, see its
[task definition](https://github.com/cleverage/process-bundle/blob/main/docs/reference/02-task_definition.md)).
Referencing it by its class name (`service: '@CleverAge\SoapProcessBundle\Task\RequestTask'`) is deprecated since
`cleverage/process-bundle` 5.4.

The transformer is tagged with its code, `soap_request` (the transformers and the tasks have separate codes): with
`cleverage/process-bundle` 5.4 or later, it is loaded lazily, only when a process uses it.

The service id of the task and of the transformer is their class name. Fetching them directly from the service container
is deprecated since 3.3 (they will be private in 4.0); their former ids, `cleverage_soap_process.task.request` and
`cleverage_soap_process.transformer.request`, are deprecated aliases since 3.3, removed in 4.0.

## Reference

- [Client](reference/client.md)
- Tasks
  - [RequestTask](reference/tasks/request_task.md)
- Transformers
  - [RequestTransformer](reference/transformers/request_transformer.md)

## Cookbooks

- [Call a SOAP service and export the response](cookbooks/soap_call_and_export.md)
- [Enrich a CSV file with a SOAP service](cookbooks/soap_enrich_csv.md)
