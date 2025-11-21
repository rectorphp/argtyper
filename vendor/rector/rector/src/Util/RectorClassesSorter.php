<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Util;

use Argtyper202511\Rector\Contract\Rector\RectorInterface;
use Argtyper202511\Rector\PostRector\Contract\Rector\PostRectorInterface;
final class RectorClassesSorter
{
    /**
     * @param array<class-string<RectorInterface|PostRectorInterface>> $rectorClasses
     * @return array<class-string<RectorInterface>>
     */
    public static function sortAndFilterOutPostRectors(array $rectorClasses) : array
    {
        $rectorClasses = \array_unique($rectorClasses);
        $mainRectorClasses = \array_filter($rectorClasses, function (string $rectorClass) : bool {
            return \is_a($rectorClass, RectorInterface::class, \true);
        });
        \sort($mainRectorClasses);
        return $mainRectorClasses;
    }
}
