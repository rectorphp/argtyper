<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Guard;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PHPStan\Reflection\MethodReflection;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Reflection\ReflectionResolver;
final class StaticGuard
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, ReflectionResolver $reflectionResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $node
     */
    public function isLegal($node): bool
    {
        if ($node->static) {
            return \false;
        }
        $nodes = $node instanceof Closure ? $node->stmts : [$node->expr];
        return !(bool) $this->betterNodeFinder->findFirst($nodes, function (Node $subNode): bool {
            if (!$subNode instanceof StaticCall) {
                return $subNode instanceof Variable && $subNode->name === 'this';
            }
            $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromStaticCall($subNode);
            if (!$methodReflection instanceof MethodReflection) {
                return \false;
            }
            return !$methodReflection->isStatic();
        });
    }
}
