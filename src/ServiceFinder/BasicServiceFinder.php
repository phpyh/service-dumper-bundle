<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\ServiceFinder;

use PHPyh\ServiceDumperBundle\ServiceFinder;

/**
 * @api
 */
final class BasicServiceFinder implements ServiceFinder
{
    public function find(array $serviceIds, string $searchString): array
    {
        $searchString = mb_strtolower($searchString);
        $found = [];

        foreach ($serviceIds as $serviceId) {
            if (strpos(mb_strtolower($serviceId), $searchString) !== false) {
                $found[] = $serviceId;
            }
        }

        return $found;
    }
}
