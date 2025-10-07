<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;
use Rector\ArgTyper\Configuration\ClassMethodTypesConfigurationProvider;
use Rector\ArgTyper\Rector\TypeMapper\DocStringTypeMapper;
use Rector\ArgTyper\Rector\ValueObject\ClassMethodType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
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
        private readonly DocBlockUpdater $docBlockUpdater,
        private readonly DocStringTypeMapper $docStringTypeMapper
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
                if (! $this->isParamTypeArray($param)) {
                    continue;
                }

                $paramClassMethodTypes = $classMethodTypesByPosition[$position] ?? null;
                if ($paramClassMethodTypes === null) {
                    continue;
                }

                $classMethodType = $paramClassMethodTypes[0];

                $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

                /** @var string $paramName */
                $paramName = $this->getName($param->var);

                // already known
                if ($classMethodPhpDocInfo->getParamType($paramName) instanceof ParamTagValueNode) {
                    continue;
                }

                if (! $this->isUsefulArrayType($classMethodType)) {
                    continue;
                }

                $typeNode = $this->docStringTypeMapper->mapToTypeNode($classMethodType->getType());
                if (! $typeNode instanceof TypeNode) {
                    continue;
                }

                $paramTagValueNode = new ParamTagValueNode($typeNode, false, '$' . $paramName, '', false);
                $classMethodPhpDocInfo->addTagValueNode($paramTagValueNode);

                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
                $hasChanged = true;

                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
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

    private function isUsefulArrayType(ClassMethodType $classMethodType): bool
    {
        if (! str_starts_with($classMethodType->getType(), 'array')) {
            return false;
        }

        // not detailed much
        return $classMethodType->getType() !== 'array';
    }
}
