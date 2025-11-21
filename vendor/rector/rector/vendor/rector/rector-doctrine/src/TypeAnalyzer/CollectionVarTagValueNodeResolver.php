<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypeAnalyzer;

use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Doctrine\CodeQuality\Enum\CollectionMapping;
use Argtyper202511\Rector\Doctrine\NodeAnalyzer\AttributeFinder;
final class CollectionVarTagValueNodeResolver
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttributeFinder
     */
    private $attributeFinder;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, AttributeFinder $attributeFinder)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->attributeFinder = $attributeFinder;
    }
    public function resolve(Property $property) : ?VarTagValueNode
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if (!$this->hasAnnotationOrAttributeToMany($phpDocInfo, $property)) {
            return null;
        }
        return $phpDocInfo->getVarTagValueNode();
    }
    private function hasAnnotationOrAttributeToMany(PhpDocInfo $phpDocInfo, Property $property) : bool
    {
        if ($phpDocInfo->hasByAnnotationClasses(CollectionMapping::TO_MANY_CLASSES)) {
            return \true;
        }
        return $this->attributeFinder->hasAttributeByClasses($property, CollectionMapping::TO_MANY_CLASSES);
    }
}
