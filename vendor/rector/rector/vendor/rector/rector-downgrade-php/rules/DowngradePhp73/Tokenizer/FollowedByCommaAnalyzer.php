<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp73\Tokenizer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\Rector\Util\StringUtils;
use Argtyper202511\Rector\ValueObject\Application\File;
final class FollowedByCommaAnalyzer
{
    public function isFollowed(File $file, Node $node): bool
    {
        $oldTokens = $file->getOldTokens();
        $nextTokenPosition = $node->getEndTokenPos() + 1;
        while (isset($oldTokens[$nextTokenPosition])) {
            $currentToken = $oldTokens[$nextTokenPosition];
            // only space
            if (StringUtils::isMatch((string) $currentToken, '#\s+#')) {
                ++$nextTokenPosition;
                continue;
            }
            // without comma
            if (in_array((string) $currentToken, ['(', ')', ';'], \true)) {
                return \false;
            }
            break;
        }
        return \true;
    }
}
