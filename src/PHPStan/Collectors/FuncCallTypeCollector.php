<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Collectors;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ReflectionProvider;
use Rector\ArgTyper\Helpers\ReflectionChecker;
use Rector\ArgTyper\PHPStan\TypeMapper;

/**
 * @implements Collector<FuncCall, array<array{0: string, 1: string, 2: string}>>
 *
 * @see \Rector\ArgTyper\PHPStan\Rule\DumpFuncCallArgTypesRule
 *
 * @see \Rector\ArgTyper\Tests\PHPStan\DumpFuncCallArgTypesRule\DumpFuncCallArgTypesRuleTest
 */
final class FuncCallTypeCollector implements Collector
{
    private TypeMapper $typeMapper;

    public function __construct(
        private ReflectionProvider $reflectionProvider
    ) {
        $this->typeMapper = new TypeMapper();
    }

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    /**
     * @param FuncCall $node
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        // nothing to find here
        if ($node->isFirstClassCallable() || $node->getArgs() === []) {
            return null;
        }

        if (! $node->name instanceof Name) {
            return null;
        }

        if (! $this->reflectionProvider->hasFunction($node->name, $scope)) {
            return null;
        }

        $functionReflection = $this->reflectionProvider->getFunction($node->name, $scope);
        if (ReflectionChecker::shouldSkip($functionReflection)) {
            return null;
        }

        $functionArgTypes = [];
        foreach ($node->getArgs() as $key => $arg) {

            $typeString = $this->typeMapper->mapToStringIfUseful($arg, $scope);
            if (! is_string($typeString)) {
                continue;
            }

            $functionArgTypes[] = [$functionReflection->getName(), $key, $typeString];
        }

        // nothing to return
        if ($functionArgTypes === []) {
            return null;
        }

        return $functionArgTypes;
    }
}
