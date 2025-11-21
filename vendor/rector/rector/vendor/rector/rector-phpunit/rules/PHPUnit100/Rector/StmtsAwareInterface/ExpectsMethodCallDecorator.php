<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\PHPUnit100\Rector\StmtsAwareInterface;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Argtyper202511\Rector\PHPUnit\Enum\ConsecutiveVariable;
final class ExpectsMethodCallDecorator
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * Replace $this->expects(...)
     * with
     * $expects = ...
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    public function decorate(Expression $expression)
    {
        /** @var MethodCall|StaticCall|null $expectsExactlyCall */
        $expectsExactlyCall = null;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($expression, function (Node $node) use (&$expectsExactlyCall): ?MethodCall {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->name, 'expects')) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            $firstArg = $node->getArgs()[0];
            if (!$firstArg->value instanceof MethodCall && !$firstArg->value instanceof StaticCall) {
                return null;
            }
            $expectsExactlyCall = $firstArg->value;
            $node->args = [new Arg(new Variable(ConsecutiveVariable::MATCHER))];
            return $node;
        });
        // add expects() method
        if (!$expectsExactlyCall instanceof Expr) {
            $this->simpleCallableNodeTraverser->traverseNodesWithCallable($expression, function (Node $node): ?int {
                if (!$node instanceof MethodCall) {
                    return null;
                }
                if ($node->var instanceof MethodCall) {
                    return null;
                }
                $node->var = new MethodCall($node->var, 'expects', [new Arg(new Variable(ConsecutiveVariable::MATCHER))]);
                return NodeVisitor::STOP_TRAVERSAL;
            });
        }
        return $expectsExactlyCall;
    }
}
