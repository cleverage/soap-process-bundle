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

The bundle has no configuration. Declare at least one SOAP client as a service tagged `cleverage.soap.client`, its
`code` is then used by the `client` option of the task and the transformer (see [Client](reference/client.md)):

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
            - { name: cleverage.soap.client }
```

## Reference

- [Client](reference/client.md)
- Tasks
  - [RequestTask](reference/tasks/request_task.md)
- Transformers
  - [RequestTransformer](reference/transformers/request_transformer.md)

## Cookbooks

- [Call a SOAP service and export the response](cookbooks/soap_call_and_export.md)
- [Enrich a CSV file with a SOAP service](cookbooks/soap_enrich_csv.md)
