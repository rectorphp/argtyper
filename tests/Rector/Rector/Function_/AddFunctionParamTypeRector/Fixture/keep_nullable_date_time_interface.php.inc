<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture;

final class KeepNullableDateTimeInterface
{
    public function record(?\DateTimeInterface $when)
    {
    }
}
