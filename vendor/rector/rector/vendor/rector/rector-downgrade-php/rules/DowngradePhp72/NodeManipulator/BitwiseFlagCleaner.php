<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp72\NodeManipulator;

use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
final class BitwiseFlagCleaner
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
    public function cleanFuncCall(FuncCall $funcCall, BitwiseOr $bitwiseOr, string $flag, ?Expr $expr = null) : void
    {
        if ($bitwiseOr->left instanceof BitwiseOr) {
            /** @var BitwiseOr $leftLeft */
            $leftLeft = $bitwiseOr->left;
            if ($leftLeft->left instanceof ConstFetch && $this->nodeNameResolver->isName($leftLeft->left, $flag)) {
                $bitwiseOr = new BitwiseOr($leftLeft->right, $bitwiseOr->right);
            }
            /** @var BitwiseOr $leftRight */
            $leftRight = $bitwiseOr->left;
            if ($leftRight->right instanceof ConstFetch && $this->nodeNameResolver->isName($leftRight->right, $flag)) {
                $bitwiseOr = new BitwiseOr($leftRight->left, $bitwiseOr->right);
            }
            if ($bitwiseOr->left instanceof BitwiseOr) {
                $this->cleanFuncCall($funcCall, $bitwiseOr->left, $flag, $bitwiseOr->right);
                return;
            }
        }
        if ($expr instanceof Expr) {
            $bitwiseOr = new BitwiseOr($bitwiseOr, $expr);
        }
        $this->assignThirdArgsValue($funcCall, $bitwiseOr, $flag);
    }
    private function assignThirdArgsValue(FuncCall $funcCall, BitwiseOr $bitwiseOr, string $flag) : void
    {
        if ($bitwiseOr->right instanceof ConstFetch && $this->nodeNameResolver->isName($bitwiseOr->right, $flag)) {
            $bitwiseOr = $bitwiseOr->left;
        } elseif ($bitwiseOr->left instanceof ConstFetch && $this->nodeNameResolver->isName($bitwiseOr->left, $flag)) {
            $bitwiseOr = $bitwiseOr->right;
        }
        $args = $funcCall->getArgs();
        $fourthArg = $args[3] ?? null;
        if (!$fourthArg instanceof Arg) {
            return;
        }
        $fourthArg->value = $bitwiseOr;
    }
}
