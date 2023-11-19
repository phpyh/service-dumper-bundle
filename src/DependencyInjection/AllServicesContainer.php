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
     * @param ServiceProviderInterface<object> $privateServices
     */
    public function __construct(
        private readonly Container $container = new Container(),
        private readonly ServiceProviderInterface $privateServices = new ServiceLocator([]),
    ) {}

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

        return [...$containerIds, ...$privateIds];
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function get(string $id): object
    {
        try {
            return $this->privateServices->get($id);
        } catch (NotFoundExceptionInterface) {
            /** @var object */
            return $this->container->get($id);
        }
    }
}
