Latest
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
