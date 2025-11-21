<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PHPStan\Analyser\MutatingScope;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
/**
 * Handle Scope filling when there is error \PHPStan\Parser\ParserErrorsException
 * from PHPStan NodeScopeResolver
 */
final class RectorNodeScopeResolver
{
    /**
     * @param Stmt[] $stmts
     */
    public static function processNodes(array $stmts, MutatingScope $mutatingScope): void
    {
        $simpleCallableNodeTraverser = new SimpleCallableNodeTraverser();
        $simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use ($mutatingScope) {
            $node->setAttribute(AttributeKey::SCOPE, $mutatingScope);
            return null;
        });
    }
}
