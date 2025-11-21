<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Property;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\Doctrine\Enum\DoctrineClass;
use Argtyper202511\Rector\Doctrine\NodeManipulator\ToManyRelationPropertyTypeResolver;
use Argtyper202511\Rector\Doctrine\TypedCollections\NodeModifier\PropertyDefaultNullRemover;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\Property\TypedPropertyFromToManyRelationTypeRector\TypedPropertyFromToManyRelationTypeRectorTest
 */
final class TypedPropertyFromToManyRelationTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeManipulator\ToManyRelationPropertyTypeResolver
     */
    private $toManyRelationPropertyTypeResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\Doctrine\TypedCollections\NodeModifier\PropertyDefaultNullRemover
     */
    private $propertyDefaultNullRemover;
    public function __construct(ToManyRelationPropertyTypeResolver $toManyRelationPropertyTypeResolver, StaticTypeMapper $staticTypeMapper, PropertyDefaultNullRemover $propertyDefaultNullRemover)
    {
        $this->toManyRelationPropertyTypeResolver = $toManyRelationPropertyTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->propertyDefaultNullRemover = $propertyDefaultNullRemover;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add "Doctrine\\Common\\Collections\\Collection" type declaration, based on @ORM\\*toMany and @ODM\\*toMany annotations/attributes', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

class SimpleColumn
{
    /**
     * @ORM\OneToMany(targetEntity="App\Product")
     */
    private $products;

    #[ManyToMany(targetEntity: 'App\Car')]
    private $cars;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\Common\Collections\Collection;

class SimpleColumn
{
    /**
     * @ORM\OneToMany(targetEntity="App\Product")
     */
    private Collection $products;

    #[ManyToMany(targetEntity: 'App\Car')]
    private Collection $cars;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?Property
    {
        if ($node->type !== null && $this->isName($node->type, DoctrineClass::COLLECTION)) {
            return null;
        }
        $propertyType = $this->toManyRelationPropertyTypeResolver->resolve($node);
        if (!$propertyType instanceof Type || $propertyType instanceof MixedType) {
            return null;
        }
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
        if (!$typeNode instanceof Node) {
            return null;
        }
        // remove default null value if any
        $this->propertyDefaultNullRemover->remove($node);
        if (!$propertyType instanceof UnionType) {
            $node->type = $typeNode;
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
}
