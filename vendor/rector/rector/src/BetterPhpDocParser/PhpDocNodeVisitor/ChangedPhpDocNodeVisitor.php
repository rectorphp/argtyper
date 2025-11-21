<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use Argtyper202511\PHPStan\PhpDocParser\Ast\Node;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Argtyper202511\Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class ChangedPhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function beforeTraverse(Node $node): void
    {
        $this->hasChanged = \false;
    }
    public function enterNode(Node $node): ?Node
    {
        $origNode = $node->getAttribute(PhpDocAttributeKey::ORIG_NODE);
        if ($origNode === null) {
            $this->hasChanged = \true;
        }
        return null;
    }
    public function hasChanged(): bool
    {
        return $this->hasChanged;
    }
}
