<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\Helpers\FilesLoader;
use Rector\ArgTyper\Helpers\ReflectionChecker;
use Rector\ArgTyper\PHPStan\TypeMapper;

/**
 * @implements Rule<FuncCall>
 *
 * @see \Rector\ArgTyper\Tests\PHPStan\CollectFuncCallArgTypesRule\CollectFuncCallArgTypesRuleTest
 */
final readonly class CollectFuncCallArgTypesRule implements Rule
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
    public function processNode(Node $node, Scope $scope): array
    {
        // nothing to find here
        if ($node->isFirstClassCallable() || $node->getArgs() === []) {
            return [];
        }

        if (! $node->name instanceof Name) {
            return [];
        }

        if (! $this->reflectionProvider->hasFunction($node->name, $scope)) {
            return [];
        }

        $functionReflection = $this->reflectionProvider->getFunction($node->name, $scope);
        if (ReflectionChecker::shouldSkipFunctionReflection($functionReflection)) {
            return [];
        }

        foreach ($node->getArgs() as $key => $arg) {
            $typeString = $this->typeMapper->mapToStringIfUseful($arg, $scope);
            if (! is_string($typeString)) {
                continue;
            }

            FilesLoader::writeJsonl(
                ConfigFilePath::callLikes(),
                [
                    'function' => $functionReflection->getName(),
                    'position' => $key,
                    'type' => $typeString,
                ]
            );
        }

        // nothing to return, just comply with contract
        return [];
    }
}
