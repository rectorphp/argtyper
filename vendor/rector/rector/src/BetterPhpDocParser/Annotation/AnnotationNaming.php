<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\BetterPhpDocParser\Annotation;

final class AnnotationNaming
{
    public function normalizeName(string $name): string
    {
        return '@' . ltrim($name, '@');
    }
}
