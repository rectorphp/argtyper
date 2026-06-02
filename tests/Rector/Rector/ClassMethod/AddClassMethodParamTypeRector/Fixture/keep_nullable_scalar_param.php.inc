<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture;

final class KeepNullableScalarParam
{
    public function translate(?string $locale = null)
    {
    }
}
