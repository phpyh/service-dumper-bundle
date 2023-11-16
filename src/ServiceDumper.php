<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle;

/**
 * @api
 */
interface ServiceDumper
{
    /**
     * @param non-empty-array<string, object> $servicesById
     */
    public function dump(array $servicesById): void;
}
