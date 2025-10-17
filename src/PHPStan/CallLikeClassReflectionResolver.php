<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;

final class CallLikeClassReflectionResolver
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider
    ) {
    }

    public function resolve(New_|StaticCall|MethodCall $callLike, Scope $scope): ?ClassReflection
    {
        if ($callLike instanceof New_) {
            return $this->resolveNewAndStaticCall($callLike);
        }

        if ($callLike instanceof StaticCall) {
            return $this->resolveNewAndStaticCall($callLike);
        }

        $methodCallType = $scope->getType($callLike);

        return null;
    }

    private function resolveNewAndStaticCall(New_|StaticCall $callLike): ?ClassReflection
    {
        if (! $callLike->class instanceof Name) {
            return null;
        }

        $className = $callLike->class->toString();
        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        return $this->reflectionProvider->getClass($className);
    }
}
