<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Application;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\CallLike;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\NullsafeMethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\FunctionLike;
use Argtyper202511\PhpParser\Node\Stmt\Block;
use Argtyper202511\PhpParser\Node\Stmt\ClassLike;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Declare_;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\Switch_;
use Argtyper202511\PhpParser\Node\Stmt\TryCatch;
use Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
final class NodeAttributeReIndexer
{
    public static function reIndexStmtKeyNodeAttributes(Node $node) : ?Node
    {
        if (!$node instanceof StmtsAwareInterface && !$node instanceof ClassLike && !$node instanceof Declare_ && !$node instanceof Block) {
            return null;
        }
        if ($node->stmts === null) {
            return null;
        }
        $node->stmts = \array_values($node->stmts);
        // re-index stmt key under current node
        foreach ($node->stmts as $key => $childStmt) {
            $childStmt->setAttribute(AttributeKey::STMT_KEY, $key);
        }
        return $node;
    }
    public static function reIndexNodeAttributes(Node $node, bool $reIndexStmtKey = \true) : ?Node
    {
        if ($reIndexStmtKey) {
            self::reIndexStmtKeyNodeAttributes($node);
        }
        if ($node instanceof If_) {
            $node->elseifs = \array_values($node->elseifs);
            return $node;
        }
        if ($node instanceof TryCatch) {
            $node->catches = \array_values($node->catches);
            return $node;
        }
        if ($node instanceof FunctionLike) {
            /** @var ClassMethod|Function_|Closure $node */
            $node->params = \array_values($node->params);
            if ($node instanceof Closure) {
                $node->uses = \array_values($node->uses);
            }
            return $node;
        }
        if ($node instanceof CallLike) {
            /** @var FuncCall|MethodCall|New_|NullsafeMethodCall|StaticCall $node */
            $node->args = \array_values($node->args);
            return $node;
        }
        if ($node instanceof Switch_) {
            $node->cases = \array_values($node->cases);
            return $node;
        }
        return null;
    }
}
