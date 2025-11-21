<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Util;

use Argtyper202511\RectorPrefix202511\Nette\Utils\Strings;
final class StringUtils
{
    public static function isMatch(string $value, string $regex): bool
    {
        $match = Strings::match($value, $regex);
        return $match !== null;
    }
}
