<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use PHPyh\ServiceDumperBundle\TestKernel;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @covers \PHPyh\ServiceDumperBundle\DependencyInjection\CollectPrivateServicesPass
 * @covers \PHPyh\ServiceDumperBundle\DependencyInjection\ResolvePrivateServicesPass
 */
final class PrivateServicesPassesTest extends TestCase
{
    public function testTheyDoNotFailInEmptyContainer(): void
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new CollectPrivateServicesPass());
        $container->addCompilerPass(new ResolvePrivateServicesPass());

        $this->expectNotToPerformAssertions();

        $container->compile();
    }

    public function testTheyPreserveAPrivateService(): void
    {
        $kernel = $this->createKernel(static function (ContainerBuilder $container): void {
            $container->register('test', \stdClass::class);
            $container->setAlias('test.do_not_remove', 'test')->setPublic(true);
        });
        $kernel->boot();
        $container = $kernel->getContainer();

        $privateServices = $container->get('phpyh.service_dumper.private_services');

        self::assertFalse($container->has('test'));
        self::assertInstanceOf(ContainerInterface::class, $privateServices);
        self::assertTrue($privateServices->has('test'));
    }

    public function testTheyPreserveAPrivateAlias(): void
    {
        $kernel = $this->createKernel(static function (ContainerBuilder $container): void {
            $container->register('test', \stdClass::class);
            $container->setAlias('test.private', 'test');
            $container->setAlias('test.private.do_not_remove', 'test.private')->setPublic(true);
        });
        $kernel->boot();
        $container = $kernel->getContainer();

        $privateServices = $container->get('phpyh.service_dumper.private_services');

        self::assertFalse($container->has('test.private'));
        self::assertInstanceOf(ContainerInterface::class, $privateServices);
        self::assertTrue($privateServices->has('test.private'));
    }

    public function testTheySkipDottedService(): void
    {
        $kernel = $this->createKernel(static function (ContainerBuilder $container): void {
            $container->register('.test', \stdClass::class);
            $container->setAlias('.test.do_not_remove', '.test')->setPublic(true);
        });
        $kernel->boot();
        $container = $kernel->getContainer();

        $privateServices = $container->get('phpyh.service_dumper.private_services');

        self::assertFalse($container->has('.test'));
        self::assertInstanceOf(ContainerInterface::class, $privateServices);
        self::assertFalse($privateServices->has('.test'));
    }

    public function testTheySkipDottedAlias(): void
    {
        $kernel = $this->createKernel(static function (ContainerBuilder $container): void {
            $container->register('test', \stdClass::class);
            $container->setAlias('.test', 'test');
            $container->setAlias('.test.do_not_remove', '.test')->setPublic(true);
        });
        $kernel->boot();
        $container = $kernel->getContainer();

        $privateServices = $container->get('phpyh.service_dumper.private_services');

        self::assertFalse($container->has('.test'));
        self::assertInstanceOf(ContainerInterface::class, $privateServices);
        self::assertFalse($privateServices->has('.test'));
    }

    /**
     * @param callable(ContainerBuilder): void $configureContainer
     */
    private function createKernel(callable $configureContainer): Kernel
    {
        return new TestKernel(configureContainer: static function (ContainerBuilder $container) use ($configureContainer): void {
            $container->addCompilerPass(new CollectPrivateServicesPass(), PassConfig::TYPE_BEFORE_REMOVING, -32);
            $container->addCompilerPass(new ResolvePrivateServicesPass(), PassConfig::TYPE_AFTER_REMOVING);
            $configureContainer($container);
            $container->register('phpyh.service_dumper.private_services', ServiceLocator::class)
                ->setPublic(true)
                ->setArguments([[]]);
        });
    }
}
