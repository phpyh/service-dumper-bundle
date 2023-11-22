<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\DependencyInjection;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @internal
 * @psalm-internal PHPyh\ServiceDumperBundle
 */
final class AllServicesContainer
{
    /**
     * @readonly
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;
    /**
     * @var ServiceLocator<object>
     * @readonly
     */
    private $privateServices;
    /**
     * @param ServiceLocator<object> $privateServices
     */
    public function __construct(Container $container = null, ServiceLocator $privateServices = null)
    {
        $container = $container ?? new Container();
        $privateServices = $privateServices ?? new ServiceLocator([]);
        $this->container = $container;
        $this->privateServices = $privateServices;
    }
    /**
     * @return non-empty-list<string>
     */
    public function ids(): array
    {
        /**
         * Non-empty, because container always contains itself as 'service_container'.
         * @var non-empty-list<string>
         */
        return array_merge($this->container->getServiceIds(), array_keys($this->privateServices->getProvidedServices()));
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function get(string $id): object
    {
        try {
            return $this->privateServices->get($id);
        } catch (NotFoundExceptionInterface $exception) {
            /** @var object */
            return $this->container->get($id);
        }
    }
}
