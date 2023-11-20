<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @covers \PHPyh\ServiceDumperBundle\DependencyInjection\AllServicesContainer
 */
final class AllServicesContainerTest extends TestCase
{
    public function testItReturnsMainContainerServiceIds(): void
    {
        $container = new Container();
        $container->set('public', new \stdClass());
        $allServicesContainer = new AllServicesContainer($container);

        $serviceIds = $allServicesContainer->ids();

        self::assertSame(['service_container', 'public'], $serviceIds);
    }

    public function testItReturnsPrivateServiceIds(): void
    {
        $allServicesContainer = new AllServicesContainer(new Container(), $this->createLocator([
            'private' => new \stdClass(),
        ]));

        $serviceIds = $allServicesContainer->ids();

        self::assertSame(['service_container', 'private'], $serviceIds);
    }

    public function testItReturnsExistingMainContainerService(): void
    {
        $public = new \stdClass();
        $container = new Container();
        $container->set('public', $public);
        $allServicesContainer = new AllServicesContainer($container);

        $service = $allServicesContainer->get('public');

        self::assertSame($public, $service);
    }

    public function testItReturnsExistingPrivateService(): void
    {
        $private = new \stdClass();
        $allServicesContainer = new AllServicesContainer(new Container(), $this->createLocator([
            'private' => $private,
        ]));

        $service = $allServicesContainer->get('private');

        self::assertSame($private, $service);
    }

    public function testItThrowsForNonExistingService(): void
    {
        $allServicesContainer = new AllServicesContainer();

        try {
            $allServicesContainer->get('public');
        } catch (\Throwable $exception) {
            self::assertInstanceOf(NotFoundExceptionInterface::class, $exception);
        }
    }

    /**
     * @param non-empty-array<string, object> $services
     */
    private function createLocator(array $services): ServiceLocator
    {
        return new ServiceLocator(array_map(static function (object $service) : \Closure {
            return static function () use ($service) : object {
                return $service;
            };
        }, $services));
    }
}
