<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture;

final class SkipIntToFloatOverride
{
    public function passInteger(float $object)
    {
    }
}
