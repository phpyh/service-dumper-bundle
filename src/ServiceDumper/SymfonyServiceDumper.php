<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\ServiceDumper;

use PHPyh\ServiceDumperBundle\ServiceDumper;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @api
 * @codeCoverageIgnore
 */
final readonly class SymfonyServiceDumper implements ServiceDumper
{
    public function dump(object $service): void
    {
        VarDumper::dump($service);
    }
}
