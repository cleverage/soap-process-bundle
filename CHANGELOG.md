Latest
------

### Changes
* [#60](https://github.com/cleverage/soap-process-bundle/issues/60) Composer: require PHP `^8.2` instead of `>=8.2` (PHP 9 is no longer accepted before being tested), remove the authorization of the `symfony/flex` plugin, which is not required (the CI installs it globally), as [cleverage/process-bundle#319](https://github.com/cleverage/process-bundle/issues/319).
* [#55](https://github.com/cleverage/soap-process-bundle/issues/55) Define the public API (Symfony BC promise, as [cleverage/process-bundle#279](https://github.com/cleverage/process-bundle/issues/279)): `RequestTask`, `RequestTransformer`, `ClientRegistry` and `CleverAgeSoapProcessBundle` are marked `@final since 3.3`, they will be `final` in 4.0: extending them triggers a deprecation with the Symfony `DebugClassLoader`. `Client` (extended by the applications) and `MissingClientException` stay open. `CleverAgeSoapProcessExtension` and `RegisterClientsPass` are marked `@internal`.
* [#54](https://github.com/cleverage/soap-process-bundle/issues/54) `CleverAgeSoapProcessExtension::findServices()` loads the service definition files with a glob pattern instead of a `Finder` (same files, as [cleverage/process-bundle#271](https://github.com/cleverage/process-bundle/issues/271)), `symfony/finder` is no longer required. Add tests.
* [#52](https://github.com/cleverage/soap-process-bundle/issues/52) ClientRegistry: the clients are loaded lazily from a service locator indexed by code: a client is only instantiated when a task or the transformer uses it (an error in a client that a process does not use no longer makes it fail). The code is given by the new `code` attribute of the `cleverage.soap.client` tag, or by the new `#[AsSoapClient(code: '...')]` attribute (autoconfiguration); it must be the one returned by `getCode()`. Duplicate or empty codes fail the container compilation. A client tagged without `code` is still registered eagerly, with `ClientRegistry::addClient()`, which is deprecated (the `code` will be required in 4.0). New optional `ClientRegistry` constructor argument (the locator); `ClientRegistry` is autowirable by its class name. Require `symfony/service-contracts`. Update documentation, add tests.
* [#50](https://github.com/cleverage/soap-process-bundle/issues/50) Require `symfony/polyfill-php83`, `symfony/polyfill-php84` and `symfony/polyfill-php85` ([cleverage/process-bundle#299](https://github.com/cleverage/process-bundle/issues/299)), so the functions of PHP 8.3 to 8.5 can be used while supporting PHP 8.2. Configure Rector for PHP 8.2 with its polyfills set (it was configured for PHP 8.5, and could introduce PHP 8.3+ functions without polyfill). No code change: no Rector rule applies and no manual replacement keeps an identical behaviour. Document it in `CONTRIBUTING.md`.
* [#46](https://github.com/cleverage/soap-process-bundle/issues/46) Give a code to the task ([cleverage/process-bundle#91](https://github.com/cleverage/process-bundle/issues/91)): `soap_request` (`task: soap_request`, with `cleverage/process-bundle` 5.4 or later, referencing it by `service` being deprecated there). The task has its class name as service id; fetching it directly from the service container is deprecated (`container.private`), it will be private in 4.0. The former id `cleverage_soap_process.task.request` is a deprecated alias, removed in 4.0. Update documentation, add tests.
* [#45](https://github.com/cleverage/soap-process-bundle/issues/45) Tag the `RequestTransformer` with its code, `soap_request`, for its lazy loading by `cleverage/process-bundle` 5.4 or later ([cleverage/process-bundle#276](https://github.com/cleverage/process-bundle/issues/276)). The transformer has its class name as service id; fetching it directly from the service container is deprecated (`container.private`), it will be private in 4.0. The former id `cleverage_soap_process.transformer.request` is a deprecated alias, removed in 4.0. Update documentation, add tests.
* [#43](https://github.com/cleverage/soap-process-bundle/issues/43) CI: test the lowest versions allowed by `composer.json` (`--prefer-lowest`, PHP 8.2). Require `psr/log` `^2|^3`: the loggers (of the tests, and `TaskLogger` of `cleverage/process-bundle`) use the typed `LoggerInterface` signatures of `psr/log` 2+, a fatal error with `psr/log` 1.
* [#41](https://github.com/cleverage/soap-process-bundle/issues/41) Add a `.gitattributes`: the development files (`.docker/`, `.github/`, `tests/`, quality tools and PHPUnit configurations, `Makefile`) are no longer part of the archive installed by Composer (`dist`).
* [#38](https://github.com/cleverage/soap-process-bundle/issues/38) Require `psr/log`, used directly by `Client` and `RequestTask` but only installed through `cleverage/process-bundle`.

### Fixes
* [#37](https://github.com/cleverage/soap-process-bundle/issues/37) Fix RequestTask: inject the `logger` service instead of `monolog.logger`, so that it logs on the `cleverage_process_task` channel of its `monolog.logger` tag (it logged on the `app` channel, without the process context, and its logs were missing from the process execution logs of the UI). Add tests.

v3.2
------

### Changes
* [#23](https://github.com/cleverage/soap-process-bundle/issues/23) Add missing tests: Client (with a fake SoapClient), RequestTask, RequestTransformer, MissingClientException, bundle and DI extension.
* [#27](https://github.com/cleverage/soap-process-bundle/issues/27) Give the ids of both services in the error on duplicate client codes: the clients are registered by a compiler pass of the bundle, `ClientRegistry::addClient()` gets an optional `$serviceId` argument. Update documentation, add tests.

### Fixes
* [#29](https://github.com/cleverage/soap-process-bundle/issues/29) Fix RequestTask: keep the SOAP options and headers of the client definition when `soap_call_options` / `soap_call_headers` are not set (they were overwritten by `null`). Update documentation, add tests.
* [#30](https://github.com/cleverage/soap-process-bundle/issues/30) Fix RequestTask and RequestTransformer: set `soap_call_options` / `soap_call_headers` for the call only (they leaked to the next calls of the client), add these options to the `soap_request` transformer. Update documentation, add tests.
* [#31](https://github.com/cleverage/soap-process-bundle/issues/31) Fix Client: throw the `SoapFault` of a failed call (it returned `false`), so a method returning `false` is no longer handled as failed, and the `soap_request` transformer fails on a `SoapFault`. Update documentation, add tests.
* [#32](https://github.com/cleverage/soap-process-bundle/issues/32) Fix Client: a `SoapFault` returned with the `exceptions: false` option makes the call fail (it was returned as a result). Update documentation, add tests.
* [#33](https://github.com/cleverage/soap-process-bundle/issues/33) Fix RequestTask: throw an explicit `\UnexpectedValueException` on a non-array input (a `TypeError` was triggered); Client: log the notice of the calls handled by a `soapCall<Method>()` override. Update documentation, add tests.

v3.1
------

### Changes
* [#20](https://github.com/cleverage/soap-process-bundle/issues/20) Update quality stack: use Rector `withComposerBased()` sets (removed `SYMFONY_64` / `PHPUNIT_100` sets), declare used Symfony packages and PHPUnit range in composer.json, apply quality tools fixes
* [#22](https://github.com/cleverage/soap-process-bundle/issues/22) Add missing documentations: reference pages for Client, RequestTask & RequestTransformer, cookbooks. Harmonize and fix existing documentation.

### Fixes
* [#25](https://github.com/cleverage/soap-process-bundle/issues/25) RequestTask throws an exception when the SOAP call fails, so that the error strategy applies: the process now fails with the `stop` strategy, and the error outputs receive the task input (instead of `false`) with the `skip` strategy

v3.0
------

### Changes
* [#14](https://github.com/cleverage/soap-process-bundle/issues/14) Add support for PHP 8.5 and Symfony 8.* Update phpunit/phpunit to version >10.0 Bump version to cleverage/process-bundle ^5.0

### BC breaks
* [#14](https://github.com/cleverage/soap-process-bundle/issues/14) Remove support for PHP 8.1 and Symfony 7.3


v2.1
------

### Changes

* [#12](https://github.com/cleverage/soap-process-bundle/issues/12) Upgrade to Symfony 7.3 & PHP 8.4


v2.0.1
------

### Fixes

* [#10](https://github.com/cleverage/soap-process-bundle/issues/10) Add missing shared: false on tasks

v2.0
------

## BC breaks

* [#4](https://github.com/cleverage/soap-process-bundle/issues/4) Update services according to Symfony best practices.
Services should not use autowiring or autoconfiguration. Instead, all services should be defined explicitly.
Services must be prefixed with the bundle alias instead of using fully qualified class names => `cleverage_soap_process`


### Changes

* [#2](https://github.com/cleverage/soap-process-bundle/issues/2) Add Makefile & .docker for local standalone usage
* [#2](https://github.com/cleverage/soap-process-bundle/issues/2) Add rector, phpstan & php-cs-fixer configurations & apply it
* [#3](https://github.com/cleverage/soap-process-bundle/issues/3) Remove `sidus/base-bundle` dependency

### Fixes

v1.0.1
------

### Changes

* Fixed dependencies after removing sidus/base-bundle from the base process bundle

v1.0.0
------

* Initial release
