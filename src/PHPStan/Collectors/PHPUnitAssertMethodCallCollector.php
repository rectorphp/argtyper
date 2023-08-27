<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes\PHPStan\Collectors;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use TomasVotruba\SherlockTypes\Types\TypeMapper;

final class PHPUnitAssertMethodCallCollector implements Collector
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     * @return string[]|null
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if (! $node->name instanceof Identifier) {
            return null;
        }

        $methodCallName = $node->name->toString();
        if (! in_array($methodCallName, ['assertSame', 'assertEquals'], true)) {
            return null;
        }

        $assertArgs = $node->getArgs();
        if (count($assertArgs) < 2) {
            return null;
        }

        $secondArg = $assertArgs[1];
        if (! $secondArg->value instanceof MethodCall) {
            return null;
        }

        $firstArg = $assertArgs[0];
        $firstArgType = $scope->getType($firstArg->value);

        $methodCall = $secondArg->value;
        $callerType = $scope->getType($methodCall->var);
        if (! $callerType instanceof TypeWithClassName) {
            return null;
        }

        // skip classes we don't own
        $className = $callerType->getClassName();
        if (str_starts_with($className, 'Symfony\\')) {
            return null;
        }

        if (! $methodCall->name instanceof Identifier) {
            return null;
        }

        $methodName = $methodCall->name->toString();

        // unable to handle
        if ($firstArgType instanceof UnionType) {
            return null;
        }

        if ($firstArgType instanceof IntersectionType) {
            return null;
        }

        if ($firstArgType instanceof TypeWithClassName) {
            $type = 'object:' . $firstArgType->getClassName();
        } else {
            $type = TypeMapper::mapConstantToGenericTypes($firstArgType);
            $type = $type::class;
        }

        return [$type, $className, $methodName];
    }
}
