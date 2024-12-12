## Prerequisite

CleverAge/ProcessBundle must be [installed](https://github.com/cleverage/process-bundle/blob/main/docs/01-quick_start.md#installation.

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

## Reference

- Tasks
  - [RequestTask](reference/tasks/request_task.md)
- Transformers
  - [RequestTransformer]
