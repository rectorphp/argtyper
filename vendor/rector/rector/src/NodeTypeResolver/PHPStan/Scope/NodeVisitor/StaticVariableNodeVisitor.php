<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Static_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PhpParser\NodeVisitorAbstract;
use Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
use Argtyper202511\Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class StaticVariableNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function enterNode(Node $node): ?Node
    {
        if (!$node instanceof StmtsAwareInterface) {
            return null;
        }
        if ($node->stmts === null) {
            return null;
        }
        /** @var string[] $staticVariableNames */
        $staticVariableNames = [];
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof Static_) {
                $this->setIsStaticVarAttribute($stmt, $staticVariableNames);
                continue;
            }
            foreach ($stmt->vars as $staticVar) {
                $staticVariableName = $staticVar->var->name;
                if (!is_string($staticVariableName)) {
                    continue;
                }
                $staticVar->var->setAttribute(AttributeKey::IS_STATIC_VAR, \true);
                $staticVariableNames[] = $staticVariableName;
            }
        }
        return null;
    }
    /**
     * @param string[] $staticVariableNames
     */
    private function setIsStaticVarAttribute(Stmt $stmt, array $staticVariableNames): void
    {
        if ($staticVariableNames === []) {
            return;
        }
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmt, static function (Node $subNode) use ($staticVariableNames) {
            if ($subNode instanceof Class_) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$subNode instanceof Variable) {
                return null;
            }
            if ($subNode->name instanceof Expr) {
                return null;
            }
            if (!in_array($subNode->name, $staticVariableNames, \true)) {
                return null;
            }
            $subNode->setAttribute(AttributeKey::IS_STATIC_VAR, \true);
            return $subNode;
        });
    }
}
