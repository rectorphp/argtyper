<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511\Symfony\Component\Routing\Annotation;

if (\class_exists('Argtyper202511\\Symfony\\Component\\Routing\\Annotation\\Route')) {
    return;
}
class Route
{
    public function __construct($path, $name = '')
    {
    }
}
