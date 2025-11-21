<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr\Array_;
final class ArraySpreadAnalyzer
{
    public function isArrayWithUnpack(Array_ $array): bool
    {
        // Check that any item in the array is the spread
        foreach ($array->items as $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }
            if ($item->unpack) {
                return \true;
            }
        }
        return \false;
    }
}
