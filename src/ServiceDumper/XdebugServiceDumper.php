<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\ServiceDumper;

use PHPyh\ServiceDumperBundle\ServiceDumper;

/**
 * @api
 * @codeCoverageIgnore
 */
final readonly class XdebugServiceDumper implements ServiceDumper
{
    public function dump(array $servicesById): void
    {
        xdebug_break();
    }
}
