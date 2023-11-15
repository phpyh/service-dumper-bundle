# PHPyh Service Dumper Bundle

## Installation

```shell
composer require --dev phpyh/service-dumper-bundle
```

## Usage

```shell
bin/console service my_service
```

## Configuration

```yaml
phpyh_service_dumper:
  # Use on of:
  # - PHPyh\ServiceDumperBundle\ServiceDumper\NativeServiceDumper,
  # - PHPyh\ServiceDumperBundle\ServiceDumper\SymfonyServiceDumper,
  # - PHPyh\ServiceDumperBundle\ServiceDumper\XdebugServiceDumper,
  # - any valid service id with class that implements PHPyh\ServiceDumperBundle\ServiceDumper.
  service_dumper: PHPyh\ServiceDumperBundle\ServiceDumper\SymfonyServiceDumper

  # Use on of:
  # - PHPyh\ServiceDumperBundle\ServiceFinder\BasicServiceFinder,
  # - any valid service id with class that implements PHPyh\ServiceDumperBundle\ServiceFinder.
  service_finder: PHPyh\ServiceDumperBundle\ServiceFinder\BasicServiceFinder
```
