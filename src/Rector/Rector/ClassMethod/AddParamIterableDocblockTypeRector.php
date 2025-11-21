<?php

declare (strict_types=1);
namespace Rector\ArgTyper\Rector\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\ArgTyper\Configuration\CallLikeTypesConfigurationProvider;
use Rector\ArgTyper\Rector\TypeMapper\DocStringTypeMapper;
use Rector\ArgTyper\Rector\ValueObject\ClassMethodType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\Rector\AbstractRector;
/**
 * @api used in Rector config
 *
 * Load data from phpstan-collected-data and add types to parameters if not nullable
 */
final class AddParamIterableDocblockTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\ArgTyper\Configuration\CallLikeTypesConfigurationProvider
     */
    private $callLikeTypesConfigurationProvider;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\ArgTyper\Rector\TypeMapper\DocStringTypeMapper
     */
    private $docStringTypeMapper;
    public function __construct(CallLikeTypesConfigurationProvider $callLikeTypesConfigurationProvider, PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater, DocStringTypeMapper $docStringTypeMapper)
    {
        $this->callLikeTypesConfigurationProvider = $callLikeTypesConfigurationProvider;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->docStringTypeMapper = $docStringTypeMapper;
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
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->isMagic()) {
                continue;
            }
            if ($classMethod->getParams() === []) {
                continue;
            }
            $classMethodTypesByPosition = $this->callLikeTypesConfigurationProvider->matchByPosition($classMethod);
            if ($classMethodTypesByPosition === []) {
                continue;
            }
            foreach ($classMethod->getParams() as $position => $param) {
                // only look for array types
                if (!$this->isParamTypeArray($param)) {
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
                if (!$this->isUsefulArrayType($classMethodType)) {
                    continue;
                }
                $typeNode = $this->docStringTypeMapper->mapToTypeNode($classMethodType->getType());
                if (!$typeNode instanceof TypeNode) {
                    continue;
                }
                $paramTagValueNode = new ParamTagValueNode($typeNode, \false, '$' . $paramName, '', \false);
                $classMethodPhpDocInfo->addTagValueNode($paramTagValueNode);
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
                $hasChanged = \true;
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function isParamTypeArray(Param $param): bool
    {
        if (!$param->type instanceof Node) {
            return \false;
        }
        return $this->isName($param->type, 'array');
    }
    private function isUsefulArrayType(ClassMethodType $classMethodType): bool
    {
        if (strncmp($classMethodType->getType(), 'array', strlen('array')) !== 0) {
            return \false;
        }
        // not detailed much
        return $classMethodType->getType() !== 'array';
    }
}
