<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass is almost a copy of Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\TestServiceContainerRealRefPass.
 * Symfony pass is not used to avoid dependency on FrameworkBundle and test.service_container declaration details.
 *
 * @internal
 * @psalm-internal PHPyh\ServiceDumperBundle
 */
final class ResolvePrivateServicesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('phpyh.service_dumper.private_services')) {
            return;
        }

        $definitions = $container->getDefinitions();

        $privateServicesLocator = $container->getDefinition('phpyh.service_dumper.private_services');
        /** @var array<string, ServiceClosureArgument> */
        $privateServices = $privateServicesLocator->getArgument(0);

        foreach ($privateServices as $id => $argument) {
            /** @var array{Reference} */
            $values = $argument->getValues();
            $target = (string) $values[0];

            if (isset($definitions[$target])) {
                $argument->setValues([new Reference($target)]);
            } else {
                unset($privateServices[$id]);
            }
        }

        $privateServicesLocator->replaceArgument(0, $privateServices);
    }
}
