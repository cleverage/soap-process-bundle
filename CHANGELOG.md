Latest
------

### Changes
* [#20](https://github.com/cleverage/soap-process-bundle/issues/20) Update quality stack: use Rector `withComposerBased()` sets (removed `SYMFONY_64` / `PHPUNIT_100` sets), declare used Symfony packages and PHPUnit range in composer.json, apply quality tools fixes

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
