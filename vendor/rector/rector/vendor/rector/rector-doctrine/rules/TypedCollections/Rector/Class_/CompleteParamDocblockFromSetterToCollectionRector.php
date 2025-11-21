<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PHPStan\Type\Generic\GenericObjectType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Argtyper202511\Rector\Doctrine\CodeQuality\SetterCollectionResolver;
use Argtyper202511\Rector\Doctrine\TypedCollections\NodeAnalyzer\EntityLikeClassDetector;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\Class_\CompleteParamDocblockFromSetterToCollectionRector\CompleteParamDocblockFromSetterToCollectionRectorTest
 */
final class CompleteParamDocblockFromSetterToCollectionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Doctrine\TypedCollections\NodeAnalyzer\EntityLikeClassDetector
     */
    private $entityLikeClassDetector;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\CodeQuality\SetterCollectionResolver
     */
    private $setterCollectionResolver;
    public function __construct(PhpDocTypeChanger $phpDocTypeChanger, EntityLikeClassDetector $entityLikeClassDetector, PhpDocInfoFactory $phpDocInfoFactory, SetterCollectionResolver $setterCollectionResolver)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->entityLikeClassDetector = $entityLikeClassDetector;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->setterCollectionResolver = $setterCollectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Improve @param Doctrine collection types to make them useful both for PHPStan and PHPStorm', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity=Trainer::class, mappedBy="trainer")
     * @var Collection|Trainer[]
     */
    private $trainings = [];

    public function setTrainings($trainings)
    {
        $this->trainings = $trainings;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity=Trainer::class, mappedBy="trainer")
     * @var Collection|Trainer[]
     */
    private $trainings = [];

    /**
     * @param Collection<int, Trainer> $trainings
     */
    public function setTrainings($trainings)
    {
        $this->trainings = $trainings;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->entityLikeClassDetector->detect($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            $collectionObjectType = $this->setterCollectionResolver->resolveAssignedGenericCollectionType($node, $classMethod);
            if (!$collectionObjectType instanceof GenericObjectType) {
                continue;
            }
            if (count($classMethod->params) !== 1) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $param = $classMethod->params[0];
            if ($param->type instanceof Node) {
                continue;
            }
            $parameterName = $this->getName($param);
            $hasChangedParamType = $this->phpDocTypeChanger->changeParamType($classMethod, $phpDocInfo, $collectionObjectType, $param, $parameterName);
            if ($hasChangedParamType) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
