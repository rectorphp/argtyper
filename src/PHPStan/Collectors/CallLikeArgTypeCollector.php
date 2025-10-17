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
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\ArgTyper\PHPStan\CallLikeClassReflectionResolver;
use Rector\ArgTyper\PHPStan\TypeMapper;

/**
 * @implements Collector<CallLike, array<array{0: string, 1: string, 2: string, 3: string}>>
 */
final class CallLikeArgTypeCollector implements Collector
{
    private CallLikeClassReflectionResolver $callLikeClassReflectionResolver;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->callLikeClassReflectionResolver = new CallLikeClassReflectionResolver($reflectionProvider);
    }

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /**
     * @param CallLike $node
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        // nothing to find here
        if ($node->isFirstClassCallable() || $node->getArgs() === []) {
            return null;
        }

        if ($node instanceof FuncCall) {
            // @todo handle somewhere else
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
            // handle later, now work with order
            if ($arg->name instanceof Identifier) {
                continue;
            }

            $argType = $scope->getType($arg->value);
            if ($this->shouldSkipType($argType)) {
                continue;
            }

            if ($argType instanceof TypeWithClassName) {
                $type = 'object:' . $argType->getClassName();
            } else {
                $type = TypeMapper::mapConstantToGenericTypes($argType);
                $type = $type::class;
            }

            $classNameTypes[] = [$className, $methodName, $key, $type];
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

    private function shouldSkipType(Type $type): bool
    {
        // unable to move to json for now, handle later
        if ($type instanceof MixedType) {
            return true;
        }

        return $type instanceof UnionType || $type instanceof IntersectionType;
    }
}
