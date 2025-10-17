<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan;

use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;

final class CallLikeClassReflectionResolver
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider
    ) {
    }

    public function resolve(New_|StaticCall|MethodCall $callLike, \PHPStan\Analyser\Scope $scope): ?ClassReflection
    {
        if ($callLike instanceof New_) {

            // @todo
            die;
        }

        if ($callLike instanceof StaticCall) {
            if (! $callLike->class instanceof Name) {
                return null;
            }

            $className = $callLike->class->toString();
            if (! $this->reflectionProvider->hasClass($className)) {
                return null;
            }

            return $this->reflectionProvider->getClass($className);
        }

        $methodCallType = $scope->getType($callLike);

        return null;
    }
}
