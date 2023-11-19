<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass is almost a copy of Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\TestServiceContainerWeakRefPass.
 * Symfony pass is not used to avoid dependency on FrameworkBundle and test.service_container declaration details.
 *
 * @internal
 * @psalm-internal PHPyh\ServiceDumperBundle
 */
final class CollectPrivateServicesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('phpyh.service_dumper.private_services')) {
            return;
        }

        $privateServices = [];
        $definitions = $container->getDefinitions();

        foreach ($definitions as $id => $definition) {
            if ($id === '' || $id[0] === '.'
                || $definition->isPublic() && !$definition->isPrivate() && !$definition->hasTag('container.private')
                || $definition->hasErrors()
                || $definition->isAbstract()
            ) {
                continue;
            }

            $privateServices[$id] = new Reference($id, ContainerBuilder::IGNORE_ON_UNINITIALIZED_REFERENCE);
        }

        $aliases = $container->getAliases();

        foreach ($aliases as $id => $alias) {
            if ($id === '' || $id[0] === '.' || $alias->isPublic() && !$alias->isPrivate()) {
                continue;
            }

            $target = (string) $alias;

            if (isset($definitions[$target]) && !$definitions[$target]->hasErrors() && !$definitions[$target]->isAbstract()) {
                $privateServices[$id] = new Reference($target, ContainerBuilder::IGNORE_ON_UNINITIALIZED_REFERENCE);
            }
        }

        $container->getDefinition('phpyh.service_dumper.private_services')->replaceArgument(0, array_map(
            static fn (Reference $reference): ServiceClosureArgument => new ServiceClosureArgument($reference),
            $privateServices,
        ));
    }
}
