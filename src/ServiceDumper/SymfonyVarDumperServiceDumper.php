<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\ServiceDumper;

use PHPyh\ServiceDumperBundle\ServiceDumper;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @api
 * @codeCoverageIgnore
 */
final readonly class SymfonyVarDumperServiceDumper implements ServiceDumper
{
    public function dump(array $servicesById): void
    {
        foreach ($servicesById as $id => $service) {
            /** @psalm-suppress TooManyArguments */
            VarDumper::dump($service, $id);
        }
    }
}
