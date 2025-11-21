<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Global_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PhpParser\NodeVisitorAbstract;
use Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
use Argtyper202511\Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class GlobalVariableNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
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
        /** @var string[] $globalVariableNames */
        $globalVariableNames = [];
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof Global_) {
                $this->setIsGlobalVarAttribute($stmt, $globalVariableNames);
                continue;
            }
            foreach ($stmt->vars as $variable) {
                if ($variable instanceof Variable && !$variable->name instanceof Expr) {
                    $variable->setAttribute(AttributeKey::IS_GLOBAL_VAR, \true);
                    /** @var string $variableName */
                    $variableName = $variable->name;
                    $globalVariableNames[] = $variableName;
                }
            }
        }
        return null;
    }
    /**
     * @param string[] $globalVariableNames
     */
    private function setIsGlobalVarAttribute(Stmt $stmt, array $globalVariableNames): void
    {
        if ($globalVariableNames === []) {
            return;
        }
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmt, static function (Node $subNode) use ($globalVariableNames) {
            if ($subNode instanceof Class_) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$subNode instanceof Variable) {
                return null;
            }
            if ($subNode->name instanceof Expr) {
                return null;
            }
            if (!in_array($subNode->name, $globalVariableNames, \true)) {
                return null;
            }
            $subNode->setAttribute(AttributeKey::IS_GLOBAL_VAR, \true);
            return $subNode;
        });
    }
}
