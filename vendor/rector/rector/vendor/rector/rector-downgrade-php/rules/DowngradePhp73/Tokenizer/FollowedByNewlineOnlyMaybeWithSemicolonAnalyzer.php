<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp73\Tokenizer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\Rector\ValueObject\Application\File;
final class FollowedByNewlineOnlyMaybeWithSemicolonAnalyzer
{
    public function isFollowed(File $file, Node $node) : bool
    {
        $oldTokens = $file->getOldTokens();
        $nextTokenPosition = $node->getEndTokenPos() + 1;
        if (isset($oldTokens[$nextTokenPosition]) && (string) $oldTokens[$nextTokenPosition] === ';') {
            ++$nextTokenPosition;
        }
        return !isset($oldTokens[$nextTokenPosition]) || isset($oldTokens[$nextTokenPosition]) && \strncmp((string) $oldTokens[$nextTokenPosition], "\n", \strlen("\n")) === 0;
    }
}
