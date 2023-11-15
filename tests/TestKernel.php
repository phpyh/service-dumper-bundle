<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    private readonly string $tempDir;

    /**
     * @param iterable<class-string<BundleInterface>> $bundleClasses
     * @param ?callable(ContainerConfigurator, ContainerBuilder, LoaderInterface): void $configureContainer
     * @param ?callable(RoutingConfigurator): void $configureRoutes
     */
    public function __construct(
        private readonly iterable $bundleClasses = [],
        private $configureContainer = null,
        private $configureRoutes = null,
        string $environment = 'test',
        bool $debug = true,
        private readonly string $projectDir = __DIR__ . '/..',
    ) {
        parent::__construct($environment, $debug);

        $this->tempDir = uniqid(sys_get_temp_dir() . '/symfony_test_kernel/', more_entropy: true);
    }

    public function __destruct()
    {
        $this->shutdown();
    }

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();

        foreach ($this->bundleClasses as $bundleClass) {
            yield new $bundleClass();
        }
    }

    public function getProjectDir(): string
    {
        return $this->projectDir;
    }

    public function getCacheDir(): string
    {
        return $this->tempDir . '/cache';
    }

    public function getLogDir(): string
    {
        return $this->tempDir . '/log';
    }

    public function shutdown(): void
    {
        parent::shutdown();

        (new Filesystem())->remove($this->tempDir);
    }

    /**
     * @psalm-suppress UnusedMethod
     */
    private function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        if ($this->configureContainer !== null) {
            ($this->configureContainer)($container, $builder, $loader);
        }
    }

    /**
     * @psalm-suppress UnusedMethod
     */
    private function configureRoutes(RoutingConfigurator $routes): void
    {
        if ($this->configureRoutes !== null) {
            ($this->configureRoutes)($routes);
        }
    }
}
