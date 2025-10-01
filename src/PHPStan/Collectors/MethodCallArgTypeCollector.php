<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Collectors;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\ArgTyper\Types\TypeMapper;

/**
 * @implements Collector<MethodCall, array<array{0: string, 1: string, 2: string, 3: string}>>
 */
final class MethodCallArgTypeCollector implements Collector
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
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

        $callerType = $scope->getType($node->var);
        if (! $callerType->isObject()->yes()) {
            return null;
        }

        $methodCallName = $node->name->toString();

        $classNameTypes = [];

        $objectClassReflections = $callerType->getObjectClassReflections();

        foreach ($objectClassReflections as $objectClassReflection) {
            if (! $objectClassReflection->hasMethod($methodCallName)) {
                continue;
            }

            if ($objectClassReflection->isInternal()) {
                continue;
            }

            // skip vendor calls, skips we cannot modify those
            $fileName = $objectClassReflection->getFileName();
            if ($fileName === null) {
                continue;
            }

            if (str_contains($fileName, '/vendor')) {
                continue;
            }

            $className = $objectClassReflection->getName();

            foreach ($node->getArgs() as $key => $arg) {
                // handle later, now work with order
                if ($arg->name instanceof Identifier) {
                    continue;
                }

                $argType = $scope->getType($arg->value);

                // unable to move to json for now, handle later
                if ($argType instanceof UnionType || $argType instanceof IntersectionType) {
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
        }

        // avoid empty array processing in the rule
        if ($classNameTypes === []) {
            return null;
        }

        return $classNameTypes;
    }
}
