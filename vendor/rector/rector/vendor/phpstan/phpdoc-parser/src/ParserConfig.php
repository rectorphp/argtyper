<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser;

class ParserConfig
{
    /**
     * @var bool
     */
    public $useLinesAttributes;
    /**
     * @var bool
     */
    public $useIndexAttributes;
    /**
     * @var bool
     */
    public $useCommentsAttributes;
    /**
     * @param array{lines?: bool, indexes?: bool, comments?: bool} $usedAttributes
     */
    public function __construct(array $usedAttributes)
    {
        $this->useLinesAttributes = $usedAttributes['lines'] ?? \false;
        $this->useIndexAttributes = $usedAttributes['indexes'] ?? \false;
        $this->useCommentsAttributes = $usedAttributes['comments'] ?? \false;
    }
}
