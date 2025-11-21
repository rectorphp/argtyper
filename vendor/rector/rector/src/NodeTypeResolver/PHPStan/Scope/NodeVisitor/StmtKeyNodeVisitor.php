<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\NodeVisitorAbstract;
use Argtyper202511\Rector\Application\NodeAttributeReIndexer;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
final class StmtKeyNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    public function enterNode(Node $node) : ?Node
    {
        return NodeAttributeReIndexer::reIndexStmtKeyNodeAttributes($node);
    }
}
