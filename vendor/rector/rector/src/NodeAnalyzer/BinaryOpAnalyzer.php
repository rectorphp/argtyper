<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr\BinaryOp;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\ValueObject\FuncCallAndExpr;
final class BinaryOpAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function matchFuncCallAndOtherExpr(BinaryOp $binaryOp, string $funcCallName) : ?FuncCallAndExpr
    {
        if ($binaryOp->left instanceof FuncCall) {
            if (!$this->nodeNameResolver->isName($binaryOp->left, $funcCallName)) {
                return null;
            }
            return new FuncCallAndExpr($binaryOp->left, $binaryOp->right);
        }
        if ($binaryOp->right instanceof FuncCall) {
            if (!$this->nodeNameResolver->isName($binaryOp->right, $funcCallName)) {
                return null;
            }
            return new FuncCallAndExpr($binaryOp->right, $binaryOp->left);
        }
        return null;
    }
}
