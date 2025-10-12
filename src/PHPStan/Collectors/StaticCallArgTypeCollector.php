<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Collectors;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\TypeWithClassName;
use Rector\ArgTyper\PHPStan\TypeMapper;

/**
 * @implements Collector<StaticCall, array<array{0: string, 1: string, 2: string, 3: string}>>
 */
final class StaticCallArgTypeCollector extends AbstractCallLikeTypeCollector implements Collector
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider
    ) {
    }

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * @param StaticCall $node
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if (! $node->name instanceof Identifier) {
            return null;
        }

        if ($node->isFirstClassCallable()) {
            return null;
        }

        // we need at least some args
        if ($node->getArgs() === []) {
            return null;
        }

        if (! $node->class instanceof Name) {
            return null;
        }

        $className = $node->class->toString();

        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        $methodCallName = $node->name->toString();

        if (! $classReflection->hasMethod($methodCallName)) {
            return null;
        }

        if ($classReflection->isInternal()) {
            return null;
        }

        // skip vendor calls, skips we cannot modify those
        if ($this->isVendorClass($classReflection)) {
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

            $classNameTypes[] = [$className, $methodCallName, $key, $type];
        }

        // nothing to return
        if ($classNameTypes === []) {
            return null;
        }

        return $classNameTypes;
    }
}
