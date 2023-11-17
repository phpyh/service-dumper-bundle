<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle;

/**
 * @api
 */
interface ServiceFinder
{
    /**
     * @param non-empty-list<string> $serviceIds
     * @param non-empty-string $searchString
     * @return list<string>
     */
    public function find(array $serviceIds, string $searchString): array;
}
