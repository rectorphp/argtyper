<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\UnionType;
use Rector\ArgTyper\Configuration\ClassMethodTypesConfigurationProvider;
use Rector\ArgTyper\Rector\TypeResolver;
use Rector\Rector\AbstractRector;

/**
 * @api used in Rector config
 *
 * Load data from phpstan-collected-data and add types to parameters if not nullable
 */
final class AddParamIterableDocblockTypeRector extends AbstractRector
{
    public function __construct(
        private readonly ClassMethodTypesConfigurationProvider $classMethodTypesConfigurationProvider
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

                $isNullable = $this->isNullable($param);
                $typeNode = TypeResolver::resolveTypeNode($classMethodType->getType());

                if ($classMethodType->isObjectType() && $param->type instanceof Name) {
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

    private function isNullable(Param $param): bool
    {
        if ($param->type instanceof NullableType) {
            return true;
        }

        if (! $param->default instanceof ConstFetch) {
            return false;
        }

        return $this->isName($param->default, 'null');
    }
}
