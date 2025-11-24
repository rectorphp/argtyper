<?php

declare (strict_types=1);
namespace Rector\ArgTyper\PHPStan;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use Rector\ArgTyper\Configuration\ProjectAutoloadGuard;
final class CallLikeClassReflectionResolver
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\ArgTyper\Configuration\ProjectAutoloadGuard
     */
    private $projectAutoloadGuard;
    public function __construct(ReflectionProvider $reflectionProvider, ProjectAutoloadGuard $projectAutoloadGuard)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->projectAutoloadGuard = $projectAutoloadGuard;
    }
    /**
     * @param \PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\NullsafeMethodCall $callLike
     */
    public function resolve($callLike, Scope $scope): ?ClassReflection
    {
        if ($callLike instanceof New_ || $callLike instanceof StaticCall) {
            return $this->resolveNewAndStaticCall($callLike);
        }
        $methodCallerType = $scope->getType($callLike->var);
        $this->projectAutoloadGuard->ensureProjectAutoloadFileIsLoaded($methodCallerType);
        // @todo check if this can be less strict, e.g. for nullable etc.
        if (!$methodCallerType->isObject()->yes()) {
            return null;
        }
        // unwrap "self::" and "$this" calls
        if ($methodCallerType instanceof StaticType) {
            $methodCallerType = $methodCallerType->getStaticObjectType();
        }
        if ($methodCallerType instanceof ObjectType) {
            return $methodCallerType->getClassReflection();
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\StaticCall $callLike
     */
    private function resolveNewAndStaticCall($callLike): ?ClassReflection
    {
        if (!$callLike->class instanceof Name) {
            return null;
        }
        $className = $callLike->class->toString();
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        return $this->reflectionProvider->getClass($className);
    }
}
