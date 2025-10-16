<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\AddParamTypeRector\Fixture;

final class KeepNullableDateTimeInterface
{
    public function record(?\DateTimeInterface $when)
    {
    }
}
