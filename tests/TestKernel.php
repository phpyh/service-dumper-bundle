<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class TestKernel extends Kernel
{
    private readonly string $tempDir;

    /**
     * @param iterable<class-string<BundleInterface>> $bundleClasses
     * @param ?callable(ContainerBuilder): void $configureContainer
     */
    public function __construct(
        private readonly iterable $bundleClasses = [],
        private $configureContainer = null,
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

    public function registerContainerConfiguration(LoaderInterface $loader): void {}

    protected function build(ContainerBuilder $container): void
    {
        $container->setParameter('kernel.secret', 'secret');

        if ($this->configureContainer !== null) {
            ($this->configureContainer)($container);
        }
    }
}
