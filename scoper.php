<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

// see https://github.com/humbug/php-scoper
return [
    'prefix' => 'ArgTyper' . date('Ym'),
    'expose-constants' => ['#^SYMFONY\_[\p{L}_]+$#'],
    'exclude-namespaces' => ['#^TomasVotruba\\\\ArgTyper#', '#^Symfony\\\\Polyfill#'],
];