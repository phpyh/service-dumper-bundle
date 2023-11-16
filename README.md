# PHPyh Service Dumper Bundle

[![PHP Version Require](http://poser.pugx.org/phpyh/service-dumper-bundle/require/php)](https://packagist.org/packages/phpyh/service-dumper-bundle)
[![Latest Stable Version](https://poser.pugx.org/phpyh/service-dumper-bundle/v/stable.png)](https://packagist.org/packages/phpyh/service-dumper-bundle)
[![Total Downloads](https://poser.pugx.org/phpyh/service-dumper-bundle/downloads.png)](https://packagist.org/packages/phpyh/service-dumper-bundle)
[![psalm-level](https://shepherd.dev/github/phpyh/service-dumper-bundle/level.svg)](https://shepherd.dev/github/phpyh/service-dumper-bundle)
[![type-coverage](https://shepherd.dev/github/phpyh/service-dumper-bundle/coverage.svg)](https://shepherd.dev/github/phpyh/service-dumper-bundle)
[![Code Coverage](https://codecov.io/gh/phpyh/service-dumper-bundle/branch/0.3.x/graph/badge.svg)](https://codecov.io/gh/phpyh/service-dumper-bundle)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fphpyh%2Fservice-dumper-bundle%2F0.3.x)](https://dashboard.stryker-mutator.io/reports/github.com/phpyh/service-dumper-bundle/0.3.x)

## Installation

```shell
composer require --dev phpyh/service-dumper-bundle
```

## Usage

```shell
bin/console service my_service another_service
```

## Configuration

### service_dumper

```yaml
phpyh_service_dumper:
    service_dumper: symfony_var_dumper
```

You can use `var_dump`, `symfony_var_dumper`, `xdebug` or any valid service id with class that implements `PHPyh\ServiceDumperBundle\ServiceDumper`.

By default, `symfony_var_dumper` is used if [Symfony VarDumper](https://symfony.com/doc/current/components/var_dumper.html) component is available, `var_dump` otherwise.

### service_finder

```yaml
phpyh_service_dumper:
    service_finder: basic
```

You can use `basic` or any valid service id with class that implements `PHPyh\ServiceDumperBundle\ServiceFinder`.
