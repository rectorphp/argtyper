<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\Rector\Doctrine\Enum\DoctrineClass;
use Argtyper202511\Rector\Doctrine\TypedCollections\NodeAnalyzer\EntityLikeClassDetector;
use Argtyper202511\Rector\Doctrine\TypedCollections\NodeAnalyzer\InitializedArrayCollectionPropertyResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\Class_\RemoveNullFromInstantiatedArrayCollectionPropertyRector\RemoveNullFromInstantiatedArrayCollectionPropertyRectorTest
 */
final class RemoveNullFromInstantiatedArrayCollectionPropertyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Doctrine\TypedCollections\NodeAnalyzer\EntityLikeClassDetector
     */
    private $entityLikeClassDetector;
    /**
     * @readonly
     * @var \Rector\Doctrine\TypedCollections\NodeAnalyzer\InitializedArrayCollectionPropertyResolver
     */
    private $initializedArrayCollectionPropertyResolver;
    public function __construct(EntityLikeClassDetector $entityLikeClassDetector, InitializedArrayCollectionPropertyResolver $initializedArrayCollectionPropertyResolver)
    {
        $this->entityLikeClassDetector = $entityLikeClassDetector;
        $this->initializedArrayCollectionPropertyResolver = $initializedArrayCollectionPropertyResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove nullability from instantiated ArrayCollection properties, set it to Collection', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class SomeClass
{
    private ?Collection $trainings = null;

    public function __construct()
    {
        $this->trainings = new ArrayCollection();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class SomeClass
{
    private Collection $trainings;

    public function __construct()
    {
        $this->trainings = new ArrayCollection();
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
        $propertyNames = $this->initializedArrayCollectionPropertyResolver->resolve($node);
        if ($propertyNames === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if (!$this->isNames($property, $propertyNames)) {
                continue;
            }
            if ($property->props[0]->default instanceof Expr) {
                $property->props[0]->default = null;
                $hasChanged = \true;
            }
            // has already correct type
            if ($property->type instanceof Name && $this->isName($property->type, DoctrineClass::COLLECTION)) {
                continue;
            }
            $property->type = new FullyQualified(DoctrineClass::COLLECTION);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
