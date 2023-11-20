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
    /**
     * @readonly
     * @var \Symfony\Component\VarDumper\Dumper\DataDumperInterface
     */
    private $dumper;
    /**
     * @readonly
     * @var \Symfony\Component\VarDumper\Cloner\ClonerInterface
     */
    private $cloner;
    public function __construct(DataDumperInterface $dumper = null, ClonerInterface $cloner = null)
    {
        $dumper = $dumper ?? new CliDumper();
        $cloner = $cloner ?? new VarCloner();
        $this->dumper = $dumper;
        $this->cloner = $cloner;
    }
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
