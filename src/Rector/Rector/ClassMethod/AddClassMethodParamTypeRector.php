<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Rector\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\ResourceType;
use Rector\ArgTyper\Configuration\CallLikeTypesConfigurationProvider;
use Rector\ArgTyper\Rector\NodeTypeChecker;
use Rector\ArgTyper\Rector\TypeResolver;
use Rector\ArgTyper\Rector\ValueObject\ClassMethodType;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;

/**
 * @api used in rector custom config
 *
 * Load data from phpstan-collected-data and add types to parameters if not nullable
 *
 * @see \Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\AddClassMethodParamTypeRectorTest
 */
final class AddClassMethodParamTypeRector extends AbstractRector
{
    public function __construct(
        private readonly CallLikeTypesConfigurationProvider $callLikeTypesConfigurationProvider,
        private readonly ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard
    ) {
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Class_
    {
        $hasChanged = false;

        foreach ($node->getMethods() as $classMethod) {
            if ($this->shouldSkipClassMethod($classMethod)) {
                continue;
            }

            $classMethodTypesByPosition = $this->callLikeTypesConfigurationProvider->matchByPosition($classMethod);
            if ($classMethodTypesByPosition === []) {
                continue;
            }

            if ($this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($classMethod)) {
                continue;
            }

            foreach ($classMethod->getParams() as $position => $param) {
                // skip as already has complex type
                if ($param->type instanceof UnionType || $param->type instanceof IntersectionType) {
                    continue;
                }

                $paramClassMethodTypes = $classMethodTypesByPosition[$position] ?? null;
                if ($paramClassMethodTypes === null) {
                    continue;
                }

                $classMethodType = $paramClassMethodTypes[0];

                // nothing useful in type declarations
                if (in_array(
                    $classMethodType->getType(),
                    [NullType::class, ResourceType::class, NeverType::class],
                    true
                )) {
                    continue;
                }

                $isNullable = NodeTypeChecker::isParamNullable($param);
                $typeNode = TypeResolver::resolveTypeNode($classMethodType->getType());

                if ($this->shouldSkipOverride($param, $classMethodType)) {
                    continue;
                }

                if ($classMethodType->isObjectType() && ($param->type instanceof Name || ($param->type instanceof NullableType && $param->type->type instanceof Name))) {
                    // skip already set object type
                    continue;
                }

                if ($isNullable) {
                    $param->type = new NullableType($typeNode);
                    $hasChanged = true;
                } else {
                    $param->type = $typeNode;
                    $hasChanged = true;
                }
            }
        }

        if (! $hasChanged) {
            return null;
        }

        return $node;
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        // empty params
        if ($classMethod->getParams() === []) {
            return true;
        }

        if ($classMethod->name->toString() === MethodName::CONSTRUCT) {
            return false;
        }

        return $classMethod->isMagic();
    }

    private function shouldSkipOverride(Param $param, ClassMethodType $classMethodType): bool
    {
        if ($param->type instanceof NullableType) {
            $rawType = $param->type->type;
        } else {
            $rawType = $param->type;
        }

        // just to be safe
        if ($rawType instanceof Identifier && in_array($rawType->toString(), ['iterable', 'float'], true)) {
            return true;
        }

        // skip already set object type
        return $classMethodType->isObjectType() && $rawType instanceof Name;
    }
}
