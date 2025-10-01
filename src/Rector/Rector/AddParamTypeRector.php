<?php

namespace Rector\ArgTyper\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use Rector\ArgTyper\Configuration\ClassMethodTypesConfigurationProvider;
use Rector\ArgTyper\Rector\ValueObject\ClassMethodType;
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

        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->isMagic()) {
                continue;
            }

            foreach ($classMethodTypes as $classMethodType) {
                if (! $this->isName($classMethod, $classMethodType->getMethod())) {
                    continue;
                }

                // match type
            }
        }

        return null;
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