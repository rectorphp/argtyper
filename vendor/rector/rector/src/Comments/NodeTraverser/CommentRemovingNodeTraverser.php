<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Comments\NodeTraverser;

use Argtyper202511\PhpParser\NodeTraverser;
use Argtyper202511\Rector\Comments\NodeVisitor\CommentRemovingNodeVisitor;
final class CommentRemovingNodeTraverser extends NodeTraverser
{
    public function __construct(CommentRemovingNodeVisitor $commentRemovingNodeVisitor)
    {
        parent::__construct($commentRemovingNodeVisitor);
    }
}
