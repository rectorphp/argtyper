<?php

declare(strict_types=1);

use Tracy\Dumper;

function d(mixed $variables): void {
    Dumper::dump($variables, [
        'depth' => 2,
    ]);
}

function dd(mixed $variables): never {
    d($variables);
    die;
}
