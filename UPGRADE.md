Upgrade Guide
=============

## v3.3

Unless stated otherwise, the deprecations below trigger a notice (`Since cleverage/soap-process-bundle 3.3: ...`) and
will be removed in 4.0.

### Process bundle 5.4

`cleverage/process-bundle` `^5.4` is required: follow its
[upgrade guide](https://github.com/cleverage/process-bundle/blob/main/UPGRADE.md#v54).

### Configuration key

The configuration key of the bundle is `cleverage_soap_process`: `clever_age_soap_process` is no longer recognized.
The bundle has no configuration, so remove the key: configuring one under `cleverage_soap_process` is deprecated (the
keys were ignored), it will be rejected in 4.0.

### Task code and service ids

Reference the task by its code instead of its service:

```yaml
tasks:
    request:
        # Before (deprecated)
        service: '@CleverAge\SoapProcessBundle\Task\RequestTask'
        # After
        task: soap_request
```

| Former service id (deprecated alias)         | Class                | Code           |
|----------------------------------------------|----------------------|----------------|
| `cleverage_soap_process.task.request`        | `RequestTask`        | `soap_request` |
| `cleverage_soap_process.transformer.request` | `RequestTransformer` | `soap_request` |

The service id of the task and the transformer is their class name. Fetching them from the service container
(`$container->get()`) is deprecated: they will be private in 4.0.

### Client codes

Registering a client without code is deprecated: declare its code (the one returned by `getCode()`) with the
`#[AsSoapClient(code: 'my_client')]` attribute or the `code` attribute of the `cleverage.soap.client` tag, so that it
is only instantiated when used. `ClientRegistry::addClient()` is deprecated.

```yaml
# config/services.yaml
App\Soap\MyClient:
    tags: [{ name: cleverage.soap.client, code: my_client }]
```

### Final classes

`RequestTask`, `RequestTransformer`, `ClientRegistry` and `CleverAgeSoapProcessBundle` are marked `@final`, they will
be `final` in 4.0 (extending them triggers a deprecation with the Symfony `DebugClassLoader`). `Client` and
`MissingClientException` stay open. `CleverAgeSoapProcessExtension` and `RegisterClientsPass` are `@internal`.

### Exceptions

The bundle throws exceptions implementing `ProcessExceptionInterface` of the process bundle, extending the SPL
exceptions thrown before: existing `catch` blocks keep working.
