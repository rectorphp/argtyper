<?php

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
