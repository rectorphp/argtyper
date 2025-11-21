<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\BetterPhpDocParser\Comment;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
final class CommentsMerger
{
    /**
     * @param Node[] $mergedNodes
     */
    public function keepComments(Node $newNode, array $mergedNodes) : void
    {
        $comments = $newNode->getComments();
        foreach ($mergedNodes as $mergedNode) {
            $comments = \array_merge($comments, $mergedNode->getComments());
        }
        if ($comments === []) {
            return;
        }
        $newNode->setAttribute(AttributeKey::COMMENTS, $comments);
        // remove so comments "win"
        $newNode->setAttribute(AttributeKey::PHP_DOC_INFO, null);
    }
}
