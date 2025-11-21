<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\BetterPhpDocParser\Printer;

use Argtyper202511\RectorPrefix202511\Nette\Utils\Strings;
final class DocBlockInliner
{
    /**
     * @var string
     * @see https://regex101.com/r/Mjb0qi/3
     */
    private const NEWLINE_CLOSING_DOC_REGEX = "#(?:\r\n|\n) \\*\\/\$#";
    /**
     * @var string
     * @see https://regex101.com/r/U5OUV4/4
     */
    private const NEWLINE_MIDDLE_DOC_REGEX = "#(?:\r\n|\n) \\* #";
    public function inline(string $docContent) : string
    {
        $docContent = Strings::replace($docContent, self::NEWLINE_MIDDLE_DOC_REGEX, ' ');
        return Strings::replace($docContent, self::NEWLINE_CLOSING_DOC_REGEX, ' */');
    }
}
