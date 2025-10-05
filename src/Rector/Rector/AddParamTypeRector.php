<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\UnionType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use Rector\ArgTyper\Configuration\ClassMethodTypesConfigurationProvider;
use Rector\ArgTyper\Exception\NotImplementedException;
use Rector\Rector\AbstractRector;

/**
 * @api used in Rector config
 *
 * Load data from phpstan-collected-data and add types to parameters if not nullable
 */
final class AddParamTypeRector extends AbstractRector
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
        $className = $this->resolveClassName($node);
        if (! is_string($className)) {
            return null;
        }

        $hasChanged = false;

        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->isMagic()) {
                continue;
            }

            $classMethodTypes = $this->classMethodTypesConfigurationProvider->match($classMethod);
            if ($classMethodTypes === []) {
                continue;
            }

            foreach ($classMethodTypes as $classMethodType) {
                if (! $this->isName($classMethod, $classMethodType->getMethod())) {
                    continue;
                }

                // temporary
                if ($classMethod->name->toString() !== 'createSellOptions') {
                    continue;
                }

                if ($className !== $classMethodType->getClass()) {
                    continue;
                }

                foreach ($classMethod->getParams() as $position => $param) {
                    // skip as already has complex type
                    if ($param->type instanceof UnionType || $param->type instanceof IntersectionType) {
                        continue;
                    }

                    if ($classMethodType->getPosition() !== $position) {
                        continue 2;
                    }

                    $isNullable = $this->isNullable($param);
                    $typeNode = $this->resolveTypeNode($classMethodType->getType());

                    if ($classMethodType->isObjectType() && $param->type instanceof Name) {
                        // already has a type
                        continue 2;
                    }

                    if ($isNullable) {
                        $param->type = new NullableType($typeNode);
                        $hasChanged = true;
                    } else {
                        $param->type = $typeNode;
                        $hasChanged = true;
                    }

                    continue 2;
                }
            }
        }

        if (! $hasChanged) {
            return null;
        }

        return $node;
    }

    private function resolveTypeNode(string $type): FullyQualified|Identifier
    {
        if (str_starts_with($type, 'object:')) {
            return new FullyQualified(substr($type, 7));
        }

        if (in_array($type, [ArrayType::class, ConstantArrayType::class], true)) {
            return new Identifier('array');
        }

        if ($type === StringType::class) {
            return new Identifier('string');
        }

        if ($type === IntegerType::class) {
            return new Identifier('int');
        }

        if ($type === FloatType::class) {
            return new Identifier('float');
        }

        if ($type === BooleanType::class) {
            return new Identifier('bool');
        }

        throw new NotImplementedException($type);
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

    private function resolveClassName(Class_ $class): ?string
    {
        if ($class->isAnonymous()) {
            return null;
        }

        // we need FQN class name
        if (! $class->namespacedName instanceof Name) {
            return null;
        }

        return $class->namespacedName->toString();
    }
}
