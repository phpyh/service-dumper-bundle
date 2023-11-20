<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\DependencyInjection;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\Service\ServiceProviderInterface;

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
     * @var ServiceProviderInterface<object>
     * @readonly
     */
    private $privateServices;
    /**
     * @param ServiceProviderInterface<object> $privateServices
     */
    public function __construct(Container $container = null, ServiceProviderInterface $privateServices = null)
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
         * Non empty, because it always contains itself as 'service_container'.
         * @var non-empty-list<string>
         */
        $containerIds = $this->container->getServiceIds();
        $privateIds = array_keys($this->privateServices->getProvidedServices());

        return array_merge(is_array($containerIds) ? $containerIds : iterator_to_array($containerIds), $privateIds);
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
