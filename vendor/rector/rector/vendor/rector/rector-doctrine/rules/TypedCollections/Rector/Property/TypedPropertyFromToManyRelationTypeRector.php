<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\Property;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\UnionType;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Doctrine\NodeManipulator\ToManyRelationPropertyTypeResolver;
use Rector\Doctrine\TypedCollections\NodeModifier\PropertyDefaultNullRemover;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
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
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add "Doctrine\Common\Collections\Collection" type declaration, based on @ORM\*toMany and @ODM\*toMany annotations/attributes', [new CodeSample(<<<'CODE_SAMPLE'
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
    public function getNodeTypes(): array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Property
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
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
}
