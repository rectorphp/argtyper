<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Comments\NodeVisitor;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\NodeVisitorAbstract;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
final class CommentRemovingNodeVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node): Node
    {
        // the node must be cloned, so original node is not touched in final print
        $clonedNode = clone $node;
        $clonedNode->setAttribute(AttributeKey::COMMENTS, []);
        $clonedNode->setAttribute(AttributeKey::PHP_DOC_INFO, null);
        return $clonedNode;
    }
}
