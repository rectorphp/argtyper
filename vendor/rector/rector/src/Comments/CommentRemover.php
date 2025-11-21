<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Comments;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\Rector\Comments\NodeTraverser\CommentRemovingNodeTraverser;
/**
 * @see \Rector\Tests\Comments\CommentRemover\CommentRemoverTest
 */
final class CommentRemover
{
    /**
     * @readonly
     * @var \Rector\Comments\NodeTraverser\CommentRemovingNodeTraverser
     */
    private $commentRemovingNodeTraverser;
    public function __construct(CommentRemovingNodeTraverser $commentRemovingNodeTraverser)
    {
        $this->commentRemovingNodeTraverser = $commentRemovingNodeTraverser;
    }
    /**
     * @param Node[]|Node|null $node
     * @return Node[]|null
     */
    public function removeFromNode($node): ?array
    {
        if ($node === null) {
            return null;
        }
        $nodes = is_array($node) ? $node : [$node];
        return $this->commentRemovingNodeTraverser->traverse($nodes);
    }
}
