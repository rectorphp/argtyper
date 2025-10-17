<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Rector\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\UnionType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\ResourceType;
use Rector\ArgTyper\Configuration\CallLikeTypesConfigurationProvider;
use Rector\ArgTyper\Rector\NodeTypeChecker;
use Rector\ArgTyper\Rector\TypeResolver;
use Rector\Rector\AbstractRector;
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
        private readonly CallLikeTypesConfigurationProvider $classMethodTypesConfigurationProvider,
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
            if ($classMethod->isMagic() || $classMethod->getParams() === []) {
                continue;
            }

            $classMethodTypesByPosition = $this->classMethodTypesConfigurationProvider->matchByPosition($classMethod);
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

                // nothing useful
                if (in_array($classMethodType->getType(), [NullType::class, ResourceType::class, NeverType::class])) {
                    continue;
                }

                $isNullable = NodeTypeChecker::isParamNullable($param);
                $typeNode = TypeResolver::resolveTypeNode($classMethodType->getType());

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
}
