<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle;

/**
 * @api
 */
interface ServiceDumper
{
    public function dump(object $service): void;
}
