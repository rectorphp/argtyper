<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\ExpectedNameResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassLike;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Naming\Naming\PropertyNaming;
use Argtyper202511\Rector\Naming\ValueObject\ExpectedName;
use Argtyper202511\Rector\NodeManipulator\PropertyManipulator;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
final class MatchPropertyTypeExpectedNameResolver
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
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
    /**
     * @readonly
     * @var \Rector\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(PropertyNaming $propertyNaming, PhpDocInfoFactory $phpDocInfoFactory, NodeNameResolver $nodeNameResolver, PropertyManipulator $propertyManipulator, ReflectionResolver $reflectionResolver, StaticTypeMapper $staticTypeMapper)
    {
        $this->propertyNaming = $propertyNaming;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyManipulator = $propertyManipulator;
        $this->reflectionResolver = $reflectionResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function resolve(Property $property, ClassLike $classLike) : ?string
    {
        if (!$classLike instanceof Class_) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($property);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        if ($this->propertyManipulator->isUsedByTrait($classReflection, $propertyName)) {
            return null;
        }
        $expectedName = $this->resolveExpectedName($property);
        if (!$expectedName instanceof ExpectedName) {
            return null;
        }
        // skip if already has suffix
        if (\substr_compare($propertyName, $expectedName->getName(), -\strlen($expectedName->getName())) === 0 || \substr_compare($propertyName, \ucfirst($expectedName->getName()), -\strlen(\ucfirst($expectedName->getName()))) === 0) {
            return null;
        }
        return $expectedName->getName();
    }
    private function resolveExpectedName(Property $property) : ?ExpectedName
    {
        // property type first
        if ($property->type instanceof Node) {
            $propertyType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($property->type);
            return $this->propertyNaming->getExpectedNameFromType($propertyType);
        }
        // fallback to docblock
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        $hasVarTag = $phpDocInfo instanceof PhpDocInfo && $phpDocInfo->getVarTagValueNode() instanceof VarTagValueNode;
        if ($hasVarTag) {
            return $this->propertyNaming->getExpectedNameFromType($phpDocInfo->getVarType());
        }
        return null;
    }
}
