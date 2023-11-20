<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\ServiceDumper;

use PHPyh\ServiceDumperBundle\ServiceDumper;
use Symfony\Component\VarDumper\Caster\ScalarStub;
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

    private static function supportsLabels(): bool
    {
        // Both labels and ScalarStub appeared in 6.3.
        return class_exists(ScalarStub::class);
    }

    public function dump(array $servicesById): void
    {
        if (self::supportsLabels()) {
            foreach ($servicesById as $id => $service) {
                $this->dumper->dump($this->cloner->cloneVar($service)->withContext(['label' => $id]));
            }

            return;
        }

        $this->dumper->dump($this->cloner->cloneVar($servicesById));
    }
}
