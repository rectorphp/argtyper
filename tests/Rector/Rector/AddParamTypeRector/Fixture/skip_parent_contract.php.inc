<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\AddParamTypeRector\Fixture;

use Rector\ArgTyper\Tests\Rector\Rector\AddParamTypeRector\Source\ParentContractInterface;

final class SkipParentContract implements ParentContractInterface
{
    public function checkItem($object)
    {
    }
}
