<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanOr;
use Argtyper202511\PhpParser\Node\Expr\CallLike;
use Argtyper202511\PhpParser\Node\Expr\Instanceof_;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticPropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\Rector\NodeAnalyzer\ExprAnalyzer;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
final class SafeLeftTypeBooleanAndOrAnalyzer
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, ExprAnalyzer $exprAnalyzer, ReflectionResolver $reflectionResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->exprAnalyzer = $exprAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BinaryOp\BooleanOr $booleanAnd
     */
    public function isSafe($booleanAnd): bool
    {
        $hasNonTypedFromParam = (bool) $this->betterNodeFinder->findFirst($booleanAnd->left, function (Node $node): bool {
            return $node instanceof Variable && $this->exprAnalyzer->isNonTypedFromParam($node);
        });
        if ($hasNonTypedFromParam) {
            return \false;
        }
        $hasPropertyFetchOrArrayDimFetch = (bool) $this->betterNodeFinder->findFirst($booleanAnd->left, static function (Node $node): bool {
            return $node instanceof PropertyFetch || $node instanceof StaticPropertyFetch || $node instanceof ArrayDimFetch;
        });
        // get type from Property and ArrayDimFetch is unreliable
        if ($hasPropertyFetchOrArrayDimFetch) {
            return \false;
        }
        // skip trait this
        $classReflection = $this->reflectionResolver->resolveClassReflection($booleanAnd);
        if ($classReflection instanceof ClassReflection && $classReflection->isTrait()) {
            return !$booleanAnd->left instanceof Instanceof_;
        }
        return !(bool) $this->betterNodeFinder->findFirst($booleanAnd->left, function (Node $node): bool {
            if (!$node instanceof CallLike) {
                return \false;
            }
            $nativeType = $this->nodeTypeResolver->getNativeType($node);
            return $nativeType instanceof MixedType && !$nativeType->isExplicitMixed();
        });
    }
}
