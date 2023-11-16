<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\ServiceDumper;

use PHPyh\ServiceDumperBundle\ServiceDumper;

/**
 * @api
 * @codeCoverageIgnore
 */
final readonly class VarDumpServiceDumper implements ServiceDumper
{
    public function dump(array $servicesById): void
    {
        /** @psalm-suppress ForbiddenCode */
        var_dump(...array_values($servicesById));
    }
}
