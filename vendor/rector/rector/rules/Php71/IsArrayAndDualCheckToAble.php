<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php71;

use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanOr;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Instanceof_;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\Rector\NodeManipulator\BinaryOpManipulator;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\Php71\ValueObject\TwoNodeMatch;
use Argtyper202511\Rector\PhpParser\Comparing\NodeComparator;
final class IsArrayAndDualCheckToAble
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(BinaryOpManipulator $binaryOpManipulator, NodeNameResolver $nodeNameResolver, NodeComparator $nodeComparator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
    }
    public function processBooleanOr(BooleanOr $booleanOr, string $type, string $newMethodName): ?FuncCall
    {
        $twoNodeMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode($booleanOr, Instanceof_::class, FuncCall::class);
        if (!$twoNodeMatch instanceof TwoNodeMatch) {
            return null;
        }
        /** @var Instanceof_ $instanceofExpr */
        $instanceofExpr = $twoNodeMatch->getFirstExpr();
        /** @var FuncCall $funcCallExpr */
        $funcCallExpr = $twoNodeMatch->getSecondExpr();
        $instanceOfClass = $instanceofExpr->class;
        if ($instanceOfClass instanceof Expr) {
            return null;
        }
        if ((string) $instanceOfClass !== $type) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($funcCallExpr, 'is_array')) {
            return null;
        }
        if ($funcCallExpr->isFirstClassCallable()) {
            return null;
        }
        if (!isset($funcCallExpr->getArgs()[0])) {
            return null;
        }
        $firstArg = $funcCallExpr->getArgs()[0];
        $firstExprNode = $firstArg->value;
        if (!$this->nodeComparator->areNodesEqual($instanceofExpr->expr, $firstExprNode)) {
            return null;
        }
        // both use same Expr
        return new FuncCall(new Name($newMethodName), [new Arg($firstExprNode)]);
    }
}
