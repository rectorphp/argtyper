<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclarationDocblocks\NodeFinder;

use Argtyper202511\PhpParser\Node\Attribute;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\DataProviderNodes;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Enum\TestClassName;
final class DataProviderMethodsFinder
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, NodeNameResolver $nodeNameResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return ClassMethod[]
     */
    public function findDataProviderNodesInClass(Class_ $class): array
    {
        $dataProviderClassMethods = [];
        foreach ($class->getMethods() as $classMethod) {
            $currentDataProviderNodes = $this->findDataProviderNodes($class, $classMethod);
            $dataProviderClassMethods = array_merge($dataProviderClassMethods, $currentDataProviderNodes->getClassMethods());
        }
        return $dataProviderClassMethods;
    }
    public function findDataProviderNodes(Class_ $class, ClassMethod $classMethod): DataProviderNodes
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if ($phpDocInfo instanceof PhpDocInfo) {
            $phpdocNodes = $phpDocInfo->getTagsByName('@dataProvider');
        } else {
            $phpdocNodes = [];
        }
        $attributes = $this->findDataProviderAttributes($classMethod);
        return new DataProviderNodes($class, $attributes, $phpdocNodes);
    }
    /**
     * @return array<Attribute>
     */
    private function findDataProviderAttributes(ClassMethod $classMethod): array
    {
        $dataProviders = [];
        foreach ($classMethod->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (!$this->nodeNameResolver->isName($attribute->name, TestClassName::DATA_PROVIDER)) {
                    continue;
                }
                $dataProviders[] = $attribute;
            }
        }
        return $dataProviders;
    }
}
