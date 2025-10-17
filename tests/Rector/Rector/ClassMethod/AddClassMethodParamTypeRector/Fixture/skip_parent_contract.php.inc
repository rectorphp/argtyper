<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture;

use Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Source\ParentContractInterface;

final class SkipParentContract implements ParentContractInterface
{
    public function checkItem($object)
    {
    }
}
