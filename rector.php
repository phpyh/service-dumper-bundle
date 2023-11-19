<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();
    $rectorConfig->cacheDirectory(__DIR__ . '/var/rector');
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
        // PHPUnitSetList::PHPUNIT_100, https://github.com/sebastianbergmann/phpunit/issues/5513
    ]);
};
