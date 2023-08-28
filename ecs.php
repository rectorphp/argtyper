<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/bin',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $ecsConfig->sets([
        SetList::COMMON,
    ]);
};
