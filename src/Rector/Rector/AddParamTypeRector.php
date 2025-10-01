<?php

namespace Rector\ArgTyper\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use Rector\ArgTyper\Configuration\ClassMethodTypesConfigurationProvider;
use Rector\Rector\AbstractRector;

/**
 * Load data from phpstan-collected-data and add types to parameters if not nullable
 */
final class AddParamTypeRector extends AbstractRector
{
    public function __construct(
        private readonly ClassMethodTypesConfigurationProvider $classMethodTypesConfigurationProvider
    )
    {

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
        // load *.json configuration
        $classMethodTypes = $this->classMethodTypesConfigurationProvider->provide();

        if ($node->isAnonymous()) {
            return null;
        }

        $hasChanged = false;

        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->isMagic()) {
                continue;
            }

            foreach ($classMethodTypes as $classMethodType) {
                if (! $this->isName($classMethod, $classMethodType->getMethod())) {
                    continue;
                }

                foreach ($classMethod->getParams() as $position => $param) {
                    $isNullable = $param->type instanceof Node\NullableType && $param->default instanceof Node\Expr\ConstFetch && $this->isName($param->default, 'null');

                    if ($classMethodType->getPosition() !== $position) {
                        continue 2;
                    }

                    $typeNode = $this->resolveTypeNode($classMethodType->getType());

                    if ($isNullable) {
                        $param->type = new Node\NullableType($typeNode);
                        $hasChanged = true;
                    } else {
                        $param->type = $typeNode;
                        $hasChanged = true;
                    }
                }
            }
        }

        if (! $hasChanged) {
            return null;
        }

        return $node;
    }

    private function resolveTypeNode(string $type): \PhpParser\Node
    {
        if (str_starts_with($type, 'object:')) {
            return new Node\Name\FullyQualified(substr($type, 7));
        }

        if (in_array($type, [ArrayType::class, ConstantArrayType::class], true)) {
            return new Identifier('array');
        }

        return new Identifier($type);
    }
}