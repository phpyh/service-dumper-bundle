<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\ServiceDumper;

use PHPyh\ServiceDumperBundle\ServiceDumper;

/**
 * @api
 * @codeCoverageIgnore
 */
final readonly class NativeServiceDumper implements ServiceDumper
{
    public function dump(object $service): void
    {
        /** @psalm-suppress ForbiddenCode */
        var_dump($service);
    }
}
