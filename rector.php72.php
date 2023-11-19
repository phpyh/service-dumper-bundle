<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\DowngradeLevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();
    $rectorConfig->cacheDirectory(__DIR__ . '/var/rector.php7');
    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);
    $rectorConfig->sets([
        DowngradeLevelSetList::DOWN_TO_PHP_72,
    ]);
};
