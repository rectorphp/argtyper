<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Argtyper202511\Rector\Doctrine\NodeAnalyzer\MethodUniqueReturnedPropertyResolver;
use Argtyper202511\Rector\Doctrine\TypeAnalyzer\CollectionTypeFactory;
use Argtyper202511\Rector\Doctrine\TypeAnalyzer\CollectionTypeResolver;
use Argtyper202511\Rector\Doctrine\TypedCollections\NodeAnalyzer\EntityLikeClassDetector;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Argtyper202511\Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 *  @see \Rector\Doctrine\Tests\TypedCollections\Rector\Class_\CompleteReturnDocblockFromToManyRector\CompleteReturnDocblockFromToManyRectorTest
 */
final class CompleteReturnDocblockFromToManyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\TypedCollections\NodeAnalyzer\EntityLikeClassDetector
     */
    private $entityLikeClassDetector;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Doctrine\TypeAnalyzer\CollectionTypeResolver
     */
    private $collectionTypeResolver;
    /**
     * @readonly
     * @var \Rector\Doctrine\TypeAnalyzer\CollectionTypeFactory
     */
    private $collectionTypeFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\MethodUniqueReturnedPropertyResolver
     */
    private $methodUniqueReturnedPropertyResolver;
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, PhpDocInfoFactory $phpDocInfoFactory, EntityLikeClassDetector $entityLikeClassDetector, PhpDocTypeChanger $phpDocTypeChanger, CollectionTypeResolver $collectionTypeResolver, CollectionTypeFactory $collectionTypeFactory, MethodUniqueReturnedPropertyResolver $methodUniqueReturnedPropertyResolver)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->entityLikeClassDetector = $entityLikeClassDetector;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->collectionTypeResolver = $collectionTypeResolver;
        $this->collectionTypeFactory = $collectionTypeFactory;
        $this->methodUniqueReturnedPropertyResolver = $methodUniqueReturnedPropertyResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Adds @return PHPDoc type to Collection property getter by *ToMany annotation/attribute', [new CodeSample(<<<'CODE_SAMPLE'
use App\Entity\Training;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
final class Trainer
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class)
     */
    private $trainings;

    public function getTrainings()
    {
        return $this->trainings;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
final class Trainer
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class)
     */
    private $trainings;

    /**
     * @return \Doctrine\Common\Collections\Collection<int, \App\Entity\Training>
     */
    public function getTrainings()
    {
        return $this->trainings;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->entityLikeClassDetector->detect($node)) {
            return null;
        }
        $hasChanged = \false;
        $scope = ScopeFetcher::fetch($node);
        foreach ($node->getMethods() as $classMethod) {
            if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($classMethod, $scope)) {
                continue;
            }
            $property = $this->methodUniqueReturnedPropertyResolver->resolve($node, $classMethod);
            if (!$property instanceof Property) {
                continue;
            }
            $collectionObjectType = $this->collectionTypeResolver->resolveFromToManyProperty($property);
            if (!$collectionObjectType instanceof FullyQualifiedObjectType) {
                continue;
            }
            // update docblock with known collection type
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $newVarType = $this->collectionTypeFactory->createType($collectionObjectType, $this->collectionTypeResolver->hasIndexBy($property), $property);
            $this->phpDocTypeChanger->changeReturnType($classMethod, $phpDocInfo, $newVarType);
            $hasChanged = \true;
        }
        return $hasChanged ? $node : null;
    }
}
