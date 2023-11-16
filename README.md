# PHPyh Service Dumper Bundle

[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fphpyh%2Fservice-dumper-bundle%2F0.2.x)](https://dashboard.stryker-mutator.io/reports/github.com/phpyh/service-dumper-bundle/0.2.x)

## Installation

```shell
composer require --dev phpyh/service-dumper-bundle
```

## Usage

```shell
bin/console service my_service
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
