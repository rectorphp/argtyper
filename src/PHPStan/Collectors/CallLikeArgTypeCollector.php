<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Collectors;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\ArgTyper\Configuration\ProjectAutoloadGuard;
use Rector\ArgTyper\PHPStan\CallLikeClassReflectionResolver;
use Rector\ArgTyper\PHPStan\TypeMapper;

/**
 * @implements Collector<CallLike, array<array{0: string, 1: string, 2: string, 3: string}>>
 *
 * @see \Rector\ArgTyper\PHPStan\Rule\DumpCallLikeArgTypesRule
 */
final class CallLikeArgTypeCollector implements Collector
{
    private CallLikeClassReflectionResolver $callLikeClassReflectionResolver;

    private TypeMapper $typeMapper;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $projectAutoloadGuard = new ProjectAutoloadGuard();
        $this->typeMapper = new TypeMapper();

        $this->callLikeClassReflectionResolver = new CallLikeClassReflectionResolver(
            $reflectionProvider,
            $projectAutoloadGuard
        );
    }

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /**
     * @param New_|FuncCall|Node\Expr\MethodCall|Node\Expr\NullsafeMethodCall|Node\Expr\StaticCall $node
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        // nothing to find here
        if ($node->isFirstClassCallable() || $node->getArgs() === []) {
            return null;
        }

        if ($node instanceof FuncCall) {
            return null;
        }

        // 1.
        if ($node instanceof New_) {
            $methodName = '__construct';
        } elseif ($node->name instanceof Identifier) {
            $methodName = $node->name->toString();
        } else {
            return null;
        }

        $classReflection = $this->callLikeClassReflectionResolver->resolve($node, $scope);

        // nothing to find here
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        if ($this->shouldSkipClassReflection($classReflection)) {
            return null;
        }

        if (! $classReflection->hasMethod($methodName)) {
            return null;
        }

        $className = $classReflection->getName();

        $classNameTypes = [];
        foreach ($node->getArgs() as $key => $arg) {
            // @todo handle later, now work with native order
            if ($arg->name instanceof Identifier) {
                continue;
            }

            $argType = $scope->getType($arg->value);

            $typeString = $this->typeMapper->mapToStringIfUseful($argType);
            if (! is_string($typeString)) {
                continue;
            }

            $classNameTypes[] = [$className, $methodName, $key, $typeString];
        }

        // nothing to return
        if ($classNameTypes === []) {
            return null;
        }

        return $classNameTypes;
    }

    private function shouldSkipClassReflection(ClassReflection $classReflection): bool
    {
        if ($classReflection->isInternal()) {
            return true;
        }

        $fileName = $classReflection->getFileName();

        // most likely internal or magic
        if ($fileName === null) {
            return true;
        }

        return str_contains($fileName, '/vendor');
    }
}
