<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\ServiceFinder;

use PHPyh\ServiceDumperBundle\ServiceFinder;

/**
 * @api
 */
final readonly class BasicServiceFinder implements ServiceFinder
{
    /**
     * @param positive-int $maxResults
     */
    public function __construct(
        private int $maxResults = 50,
    ) {}

    public function find(array $serviceIds, string $searchString): array
    {
        $searchString = mb_strtolower($searchString);
        $found = [];

        foreach ($serviceIds as $serviceId) {
            if (!str_contains(mb_strtolower($serviceId), $searchString)) {
                continue;
            }

            $found[] = $serviceId;

            if (\count($found) === $this->maxResults) {
                break;
            }
        }

        return $found;
    }
}
