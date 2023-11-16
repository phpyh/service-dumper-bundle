<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\DependencyInjection;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @internal
 * @psalm-internal PHPyh\ServiceDumperBundle
 */
final readonly class AllServicesContainer
{
    /**
     * @param ServiceProviderInterface<object> $privateServices
     */
    public function __construct(
        private Container $container,
        private ServiceProviderInterface $privateServices,
    ) {}

    /**
     * @return list<string>
     */
    public function ids(): array
    {
        /** @var list<string> */
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
