<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use Rector\ArgTyper\Configuration\ProjectAutoloadGuard;
use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\Helpers\FilesLoader;
use Rector\ArgTyper\Helpers\ReflectionChecker;
use Rector\ArgTyper\PHPStan\CallLikeClassReflectionResolver;
use Rector\ArgTyper\PHPStan\TypeMapper;

/**
 * @implements Rule<CallLike>
 *
 * @see \Rector\ArgTyper\Tests\PHPStan\CollectCallLikeArgTypesRule\CollectCallLikeArgTypesRuleTest
 */
final class CollectCallLikeArgTypesRule implements Rule
{
    private TypeMapper $typeMapper;

    private CallLikeClassReflectionResolver $callLikeClassReflectionResolver;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->typeMapper = new TypeMapper();
        $projectAutoloadGuard = new ProjectAutoloadGuard();

        $this->callLikeClassReflectionResolver = new CallLikeClassReflectionResolver(
            $reflectionProvider,
            $projectAutoloadGuard
        );
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /**
     * @param CallLike $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // nothing to find here
        if ($node->isFirstClassCallable() || $node->getArgs() === []) {
            return [];
        }

        if ($node instanceof FuncCall) {
            return [];
        }

        // 1.
        if ($node instanceof New_) {
            $methodName = '__construct';
        } elseif ($node->name instanceof Identifier) {
            $methodName = $node->name->toString();
        } else {
            return [];
        }

        $classReflection = $this->callLikeClassReflectionResolver->resolve($node, $scope);

        // nothing to find here
        if (! $classReflection instanceof ClassReflection) {
            return [];
        }

        if (ReflectionChecker::shouldSkipClassReflection($classReflection, $methodName)) {
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
                    'class' => $classReflection->getName(),
                    'method' => $methodName,
                    'position' => $key,
                    'type' => $typeString,
                ]
            );
        }

        // comply with contract, but never used
        return [];
    }
}
