<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle;

use PHPyh\ServiceDumperBundle\DependencyInjection\AllServicesContainer;
use PHPyh\ServiceDumperBundle\DependencyInjection\CollectPrivateServicesPass;
use PHPyh\ServiceDumperBundle\DependencyInjection\ResolvePrivateServicesPass;
use PHPyh\ServiceDumperBundle\ServiceDumper\SymfonyVarDumperServiceDumper;
use PHPyh\ServiceDumperBundle\ServiceDumper\VarDumpServiceDumper;
use PHPyh\ServiceDumperBundle\ServiceDumper\XdebugServiceDumper;
use PHPyh\ServiceDumperBundle\ServiceFinder\BasicServiceFinder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\VarDumper\VarDumper;

/**
 * AbstractBundle is not used to keep compatibility with Symfony 5.
 *
 * @api
 */
final class ServiceDumperBundle extends Bundle implements ConfigurationInterface
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CollectPrivateServicesPass(), PassConfig::TYPE_BEFORE_REMOVING, -32);
        $container->addCompilerPass(new ResolvePrivateServicesPass(), PassConfig::TYPE_AFTER_REMOVING);
    }

    /**
     * @psalm-suppress UndefinedMethod, MixedMethodCall, PossiblyNullReference
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('phpyh_service_dumper');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('service_dumper')
                    ->info(sprintf(
                        'Use "var_dump", "symfony_var_dumper", "xdebug" or any valid service id with class that implements %s.',
                        ServiceDumper::class,
                    ))
                    ->defaultValue(class_exists(VarDumper::class) ? 'symfony_var_dumper' : 'var_dump')
                ->end()
                ->scalarNode('service_finder')
                    ->info(sprintf(
                        'Use "basic" or any valid service id with class that implements %s.',
                        ServiceFinder::class,
                    ))
                    ->defaultValue('basic')
                ->end()
            ->end();

        return $treeBuilder;
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new class ($this) extends Extension {
            public function __construct(
                private readonly ServiceDumperBundle $bundle,
            ) {}

            public function getAlias(): string
            {
                return 'phpyh_service_dumper';
            }

            public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
            {
                return $this->bundle;
            }

            public function load(array $configs, ContainerBuilder $container): void
            {
                /** @var array{service_dumper: string, service_finder: string} $config */
                $config = $this->processConfiguration($this->bundle, $configs);
                $container->register('phpyh.service_dumper.private_services', ServiceLocator::class)->setArguments([[]]);
                $container->register(DebugDumpServiceCommand::class)
                    ->setArguments([
                        (new Definition(AllServicesContainer::class))->setArguments([
                            new Reference('service_container'),
                            new Reference('phpyh.service_dumper.private_services'),
                        ]),
                        match ($config['service_dumper']) {
                            'var_dump' => new Definition(VarDumpServiceDumper::class),
                            'symfony_var_dumper' => new Definition(SymfonyVarDumperServiceDumper::class),
                            'xdebug' => new Definition(XdebugServiceDumper::class),
                            default => new Reference($config['service_dumper']),
                        },
                        match ($config['service_finder']) {
                            'basic' => new Definition(BasicServiceFinder::class),
                            default => new Reference($config['service_finder']),
                        },
                    ])
                    ->addTag('console.command');
            }
        };
    }
}
