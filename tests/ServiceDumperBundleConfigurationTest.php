<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PHPyh\ServiceDumperBundle\ServiceDumper\SymfonyVarDumperServiceDumper;
use PHPyh\ServiceDumperBundle\ServiceDumper\VarDumpServiceDumper;
use PHPyh\ServiceDumperBundle\ServiceDumper\XdebugServiceDumper;
use PHPyh\ServiceDumperBundle\ServiceFinder\BasicServiceFinder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

#[CoversClass(ServiceDumperBundle::class)]
final class ServiceDumperBundleConfigurationTest extends TestCase
{
    public function testItUsesSymfonyVarDumperByDefault(): void
    {
        $kernel = new TestKernel([ServiceDumperBundle::class], static function (ContainerConfigurator $_di, ContainerBuilder $container): void {
            $container->addCompilerPass(new class () implements CompilerPassInterface {
                public function process(ContainerBuilder $container): void
                {
                    $commandDefinition = $container->getDefinition(DebugDumpServiceCommand::class);

                    TestCase::assertEquals(new Definition(SymfonyVarDumperServiceDumper::class), $commandDefinition->getArgument(1));
                }
            }, PassConfig::TYPE_AFTER_REMOVING);
        });
        $kernel->boot();
    }

    public function testServiceDumperCanBeSetToVarDump(): void
    {
        $kernel = new TestKernel([ServiceDumperBundle::class], static function (ContainerConfigurator $di, ContainerBuilder $container): void {
            $di->extension('phpyh_service_dumper', ['service_dumper' => 'var_dump']);

            $container->addCompilerPass(new class () implements CompilerPassInterface {
                public function process(ContainerBuilder $container): void
                {
                    $commandDefinition = $container->getDefinition(DebugDumpServiceCommand::class);

                    TestCase::assertEquals(new Definition(VarDumpServiceDumper::class), $commandDefinition->getArgument(1));
                }
            }, PassConfig::TYPE_AFTER_REMOVING);
        });
        $kernel->boot();
    }

    public function testServiceDumperCanBeSetToXdebug(): void
    {
        $kernel = new TestKernel([ServiceDumperBundle::class], static function (ContainerConfigurator $di, ContainerBuilder $container): void {
            $di->extension('phpyh_service_dumper', ['service_dumper' => 'xdebug']);

            $container->addCompilerPass(new class () implements CompilerPassInterface {
                public function process(ContainerBuilder $container): void
                {
                    $commandDefinition = $container->getDefinition(DebugDumpServiceCommand::class);

                    TestCase::assertEquals(new Definition(XdebugServiceDumper::class), $commandDefinition->getArgument(1));
                }
            }, PassConfig::TYPE_AFTER_REMOVING);
        });
        $kernel->boot();
    }

    public function testServiceDumperCanBeSetToAnyServiceId(): void
    {
        $kernel = new TestKernel([ServiceDumperBundle::class], static function (ContainerConfigurator $di, ContainerBuilder $container): void {
            $di->services()->set('test', \stdClass::class);
            $di->extension('phpyh_service_dumper', ['service_dumper' => 'test']);

            $container->addCompilerPass(new class () implements CompilerPassInterface {
                public function process(ContainerBuilder $container): void
                {
                    $commandDefinition = $container->getDefinition(DebugDumpServiceCommand::class);

                    TestCase::assertEquals(new Reference('test'), $commandDefinition->getArgument(1));
                }
            }, PassConfig::TYPE_AFTER_REMOVING);
        });
        $kernel->boot();
    }

    public function testItUsesBasicServiceFinderByDefault(): void
    {
        $kernel = new TestKernel([ServiceDumperBundle::class], static function (ContainerConfigurator $_di, ContainerBuilder $container): void {
            $container->addCompilerPass(new class () implements CompilerPassInterface {
                public function process(ContainerBuilder $container): void
                {
                    $commandDefinition = $container->getDefinition(DebugDumpServiceCommand::class);

                    TestCase::assertEquals(new Definition(BasicServiceFinder::class), $commandDefinition->getArgument(2));
                }
            }, PassConfig::TYPE_AFTER_REMOVING);
        });
        $kernel->boot();
    }

    public function testServiceFinderCanBeSetToAnyServiceId(): void
    {
        $kernel = new TestKernel([ServiceDumperBundle::class], static function (ContainerConfigurator $di, ContainerBuilder $container): void {
            $di->services()->set('test', \stdClass::class);
            $di->extension('phpyh_service_dumper', ['service_finder' => 'test']);

            $container->addCompilerPass(new class () implements CompilerPassInterface {
                public function process(ContainerBuilder $container): void
                {
                    $commandDefinition = $container->getDefinition(DebugDumpServiceCommand::class);

                    TestCase::assertEquals(new Reference('test'), $commandDefinition->getArgument(2));
                }
            }, PassConfig::TYPE_AFTER_REMOVING);
        });
        $kernel->boot();
    }
}
