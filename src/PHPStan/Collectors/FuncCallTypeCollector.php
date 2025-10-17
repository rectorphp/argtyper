<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Collectors;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\ArgTyper\PHPStan\TypeMapper;

/**
 * @implements Collector<FuncCall, array<array{0: string, 1: string, 2: string}>>
 *
 * @see \Rector\ArgTyper\PHPStan\Rule\DumpFuncCallArgTypesRule
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
        if ($this->shouldSkipClassReflection($functionReflection)) {
            return null;
        }

        $functionArgTypes = [];
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

            $functionArgTypes[] = [$functionReflection->getName(), $key, $typeString];
        }

        // nothing to return
        if ($functionArgTypes === []) {
            return null;
        }

        return $functionArgTypes;
    }

    private function shouldSkipClassReflection(FunctionReflection $functionReflection): bool
    {
        if ($functionReflection->isInternal()->yes()) {
            return true;
        }

        $fileName = $functionReflection->getFileName();

        // most likely internal or magic
        if ($fileName === null) {
            return true;
        }

        return str_contains($fileName, '/vendor');
    }
}
