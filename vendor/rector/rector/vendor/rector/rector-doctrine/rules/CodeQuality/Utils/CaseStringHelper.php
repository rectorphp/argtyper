<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality\Utils;

use Argtyper202511\RectorPrefix202511\Nette\Utils\Strings;
final class CaseStringHelper
{
    public static function camelCase(string $value) : string
    {
        $spacedValue = \str_replace('_', ' ', $value);
        $uppercasedWords = \ucwords($spacedValue);
        $spacelessWords = \str_replace(' ', '', $uppercasedWords);
        $lowercasedValue = \lcfirst($spacelessWords);
        return Strings::replace($lowercasedValue, '#\\W#', '');
    }
}
