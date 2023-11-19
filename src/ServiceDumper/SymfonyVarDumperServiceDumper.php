<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\ServiceDumper;

use PHPyh\ServiceDumperBundle\ServiceDumper;
use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;

/**
 * @api
 */
final class SymfonyVarDumperServiceDumper implements ServiceDumper
{
    public function __construct(
        private readonly DataDumperInterface $dumper = new CliDumper(),
        private readonly ClonerInterface $cloner = new VarCloner(),
    ) {}

    public function dump(array $servicesById): void
    {
        foreach ($servicesById as $id => $service) {
            $this->dumper->dump($this->cloner->cloneVar($service)->withContext(['label' => $id]));
        }
    }
}
