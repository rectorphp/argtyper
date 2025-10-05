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
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Rector\AbstractRector;

/**
 * @api used in Rector config
 *
 * Load data from phpstan-collected-data and add types to parameters if not nullable
 */
final class AddParamIterableDocblockTypeRector extends AbstractRector
{
    public function __construct(
        private readonly ClassMethodTypesConfigurationProvider $classMethodTypesConfigurationProvider,
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
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
                // only look for array types
                if ($this->isParamTypeArray($param)) {
                    continue;
                }

                $paramClassMethodTypes = $classMethodTypesByPosition[$position] ?? null;
                if ($paramClassMethodTypes === null) {
                    continue;
                }

                $classMethodType = $paramClassMethodTypes[0];

                $typeNode = TypeResolver::resolveTypeNode($classMethodType->getType());

                $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

                dump($classMethodPhpDocInfo);
                die;
            }
        }

        if (! $hasChanged) {
            return null;
        }

        return $node;
    }

    private function isParamTypeArray(Param $param): bool
    {
        if (! $param->type instanceof Node) {
            return false;
        }

        return $this->isName($param->type, 'array');
    }
}
