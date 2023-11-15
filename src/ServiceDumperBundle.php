<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle;

use PHPyh\ServiceDumperBundle\DependencyInjection\AllServicesContainer;
use PHPyh\ServiceDumperBundle\DependencyInjection\CollectPrivateServicesPass;
use PHPyh\ServiceDumperBundle\DependencyInjection\ResolvePrivateServicesPass;
use PHPyh\ServiceDumperBundle\ServiceDumper\NativeServiceDumper;
use PHPyh\ServiceDumperBundle\ServiceDumper\SymfonyServiceDumper;
use PHPyh\ServiceDumperBundle\ServiceDumper\XdebugServiceDumper;
use PHPyh\ServiceDumperBundle\ServiceFinder\BasicServiceFinder;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\VarDumper\VarDumper;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @api
 */
final class ServiceDumperBundle extends AbstractBundle
{
    protected string $extensionAlias = 'phpyh_service_dumper';

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CollectPrivateServicesPass(), PassConfig::TYPE_BEFORE_REMOVING, -32);
        $container->addCompilerPass(new ResolvePrivateServicesPass(), PassConfig::TYPE_AFTER_REMOVING);
    }

    /**
     * @psalm-suppress UndefinedMethod, MixedMethodCall
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('service_dumper')
                    ->info(sprintf(
                        'Use %s, %s, %s or any valid service id with class that implements %s.',
                        NativeServiceDumper::class,
                        SymfonyServiceDumper::class,
                        XdebugServiceDumper::class,
                        ServiceDumper::class,
                    ))
                    ->defaultValue(class_exists(VarDumper::class) ? SymfonyServiceDumper::class : NativeServiceDumper::class)
                ->end()
                ->scalarNode('service_finder')
                    ->info(sprintf(
                        'Use %s or any valid service id with class that implements %s.',
                        BasicServiceFinder::class,
                        ServiceFinder::class,
                    ))
                    ->defaultValue(BasicServiceFinder::class)
                ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        /** @var array{service_dumper: string, service_finder: string} $config */
        $container->services()
            ->set(NativeServiceDumper::class)
            ->set(SymfonyServiceDumper::class)
            ->set(XdebugServiceDumper::class)
            ->set(BasicServiceFinder::class)
            ->set('phpyh.service_dumper.private_services', ServiceLocator::class)
                ->args([[]])
            ->set(DebugDumpServiceCommand::class)
                ->args([
                    inline_service(AllServicesContainer::class)->args([
                        service('service_container'),
                        service('phpyh.service_dumper.private_services'),
                    ]),
                    service($config['service_dumper']),
                    service($config['service_finder']),
                ])
                ->tag('console.command');
    }
}
