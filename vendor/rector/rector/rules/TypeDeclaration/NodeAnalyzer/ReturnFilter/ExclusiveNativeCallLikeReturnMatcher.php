<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer\ReturnFilter;

use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PHPStan\Reflection\FunctionReflection;
use Argtyper202511\PHPStan\Reflection\MethodReflection;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
final class ExclusiveNativeCallLikeReturnMatcher
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @param Return_[] $returns
     * @return array<StaticCall|FuncCall|MethodCall>|null
     */
    public function match(array $returns): ?array
    {
        $callLikes = [];
        foreach ($returns as $return) {
            // we need exact expr return
            $returnExpr = $return->expr;
            if (!$returnExpr instanceof StaticCall && !$returnExpr instanceof MethodCall && !$returnExpr instanceof FuncCall) {
                return null;
            }
            $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($returnExpr);
            if (!$functionLikeReflection instanceof FunctionReflection && !$functionLikeReflection instanceof MethodReflection) {
                return null;
            }
            // is native func call?
            if (!$this->isNativeCallLike($functionLikeReflection)) {
                return null;
            }
            $callLikes[] = $returnExpr;
        }
        return $callLikes;
    }
    /**
     * @param \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection $functionLikeReflection
     */
    private function isNativeCallLike($functionLikeReflection): bool
    {
        if ($functionLikeReflection instanceof FunctionReflection) {
            return $functionLikeReflection->isBuiltin();
        }
        // is native method call?
        $classReflection = $functionLikeReflection->getDeclaringClass();
        return $classReflection->isBuiltin();
    }
}
