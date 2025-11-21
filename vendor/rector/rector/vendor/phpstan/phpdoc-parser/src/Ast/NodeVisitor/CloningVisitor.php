<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\NodeVisitor;

use Argtyper202511\PHPStan\PhpDocParser\Ast\AbstractNodeVisitor;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Attribute;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Node;
final class CloningVisitor extends AbstractNodeVisitor
{
    public function enterNode(Node $originalNode): Node
    {
        $node = clone $originalNode;
        $node->setAttribute(Attribute::ORIGINAL_NODE, $originalNode);
        return $node;
    }
}
