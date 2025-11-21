<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor;

use Argtyper202511\PHPStan\PhpDocParser\Ast\Node;
use Argtyper202511\Rector\PhpDocParser\PhpDocParser\ValueObject\PhpDocAttributeKey;
/**
 * @api
 *
 * Mirrors
 * https://github.com/nikic/PHP-Parser/blob/d520bc9e1d6203c35a1ba20675b79a051c821a9e/lib/PhpParser/NodeVisitor/CloningVisitor.php
 */
final class CloningPhpDocNodeVisitor extends \Argtyper202511\Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor
{
    public function enterNode(Node $node): Node
    {
        $clonedNode = clone $node;
        if (!$clonedNode->hasAttribute(PhpDocAttributeKey::ORIG_NODE)) {
            $clonedNode->setAttribute(PhpDocAttributeKey::ORIG_NODE, $node);
        }
        return $clonedNode;
    }
}
