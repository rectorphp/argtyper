<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Collectors;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\ArgTyper\PHPStan\TypeMapper;

/**
 * @implements Collector<FuncCall, array<array{0: string, 1: string, 2: string}>>
 *
 * @see \Rector\ArgTyper\PHPStan\Rule\DumpFuncCallArgTypesRule
 */
final class FuncCallTypeCollector implements Collector
{
    public function __construct(
        private ReflectionProvider $reflectionProvider
    ) {
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

            if ($this->shouldSkipType($argType)) {
                continue;
            }

            if ($argType instanceof TypeWithClassName) {
                $type = 'object:' . $argType->getClassName();
            } else {
                $type = TypeMapper::mapConstantToGenericTypes($argType);
                $type = $type::class;
            }

            $functionArgTypes[] = [$functionReflection->getName(), $key, $type];
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

    private function shouldSkipType(Type $type): bool
    {
        // unable to move to json for now, handle later
        if ($type instanceof MixedType) {
            return true;
        }

        return $type instanceof UnionType || $type instanceof IntersectionType;
    }
}
