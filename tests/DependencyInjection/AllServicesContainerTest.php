<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ServiceLocator;

#[CoversClass(AllServicesContainer::class)]
final class AllServicesContainerTest extends TestCase
{
    public function testItReturnsMainContainerServiceIds(): void
    {
        $container = new Container();
        $container->set('a', new \stdClass());
        $allServicesContainer = new AllServicesContainer($container, $this->createLocator());

        $serviceIds = $allServicesContainer->ids();

        self::assertSame(['service_container', 'a'], $serviceIds);
    }

    public function testItReturnsPrivateServiceIds(): void
    {
        $allServicesContainer = new AllServicesContainer(new Container(), $this->createLocator(['b' => new \stdClass()]));

        $serviceIds = $allServicesContainer->ids();

        self::assertSame(['service_container', 'b'], $serviceIds);
    }

    public function testItReturnsExistingMainContainerService(): void
    {
        $a = new \stdClass();
        $container = new Container();
        $container->set('a', $a);
        $allServicesContainer = new AllServicesContainer($container, $this->createLocator());

        $service = $allServicesContainer->get('a');

        self::assertSame($a, $service);
    }

    public function testItReturnsExistingPrivateService(): void
    {
        $b = new \stdClass();
        $allServicesContainer = new AllServicesContainer(new Container(), $this->createLocator(['b' => $b]));

        $service = $allServicesContainer->get('b');

        self::assertSame($b, $service);
    }

    public function testItThrowsForNonExistingService(): void
    {
        $allServicesContainer = new AllServicesContainer(new Container(), $this->createLocator());

        try {
            $allServicesContainer->get('a');
        } catch (\Throwable $exception) {
            self::assertInstanceOf(NotFoundExceptionInterface::class, $exception);
        }
    }

    /**
     * @param array<string, object> $services
     */
    private function createLocator(array $services = []): ServiceLocator
    {
        return new ServiceLocator(array_map(
            static fn (object $service): \Closure => static fn (): object => $service,
            $services,
        ));
    }
}
