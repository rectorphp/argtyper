<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\DependencyInjection\Rector\Trait_;

use Argtyper202511\PhpParser\Modifiers;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\PropertyItem;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PhpParser\Node\Stmt\Trait_;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Naming\Naming\PropertyNaming;
use Argtyper202511\Rector\PostRector\ValueObject\PropertyMetadata;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Argtyper202511\Rector\Symfony\DependencyInjection\NodeFactory\AutowireClassMethodFactory;
use Argtyper202511\Rector\Symfony\DependencyInjection\ThisGetTypeMatcher;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\DependencyInjection\Rector\Trait_\TraitGetByTypeToInjectRector\TraitGetByTypeToInjectRectorTest
 */
final class TraitGetByTypeToInjectRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @readonly
     * @var \Rector\Symfony\DependencyInjection\ThisGetTypeMatcher
     */
    private $thisGetTypeMatcher;
    /**
     * @readonly
     * @var \Rector\Symfony\DependencyInjection\NodeFactory\AutowireClassMethodFactory
     */
    private $autowireClassMethodFactory;
    public function __construct(PropertyNaming $propertyNaming, ThisGetTypeMatcher $thisGetTypeMatcher, AutowireClassMethodFactory $autowireClassMethodFactory)
    {
        $this->propertyNaming = $propertyNaming;
        $this->thisGetTypeMatcher = $thisGetTypeMatcher;
        $this->autowireClassMethodFactory = $autowireClassMethodFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('From `$this->get(SomeType::class)` in traits, to autowired method with @required', [new CodeSample(<<<'CODE_SAMPLE'
// must be used in old Controller class
trait SomeInjects
{
    public function someMethod()
    {
        return $this->get(SomeType::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
trait SomeInjects
{
    private SomeType $someType;

    /**
     * @required
     */
    public function autowireSomeInjects(SomeType $someType): void
    {
        $this->someType = $someType;
    }

    public function someMethod()
    {
        return $this->someType;
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
        return [Trait_::class];
    }
    /**
     * @param Trait_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $propertyMetadatas = [];
        $this->traverseNodesWithCallable($node, function (Node $node) use(&$propertyMetadatas) : ?Node {
            if (!$node instanceof MethodCall) {
                return null;
            }
            $className = $this->thisGetTypeMatcher->match($node);
            if (!\is_string($className)) {
                return null;
            }
            $propertyName = $this->propertyNaming->fqnToVariableName($className);
            $propertyMetadata = new PropertyMetadata($propertyName, new FullyQualifiedObjectType($className));
            $propertyMetadatas[] = $propertyMetadata;
            return $this->nodeFactory->createPropertyFetch('this', $propertyMetadata->getName());
        });
        if ($propertyMetadatas === []) {
            return null;
        }
        // create local properties
        $autowiredProperties = $this->createAutowiredProperties($propertyMetadatas);
        $autowireClassMethod = $this->autowireClassMethodFactory->create($node, $propertyMetadatas);
        $node->stmts = \array_merge($autowiredProperties, [$autowireClassMethod], $node->stmts);
        return $node;
    }
    /**
     * @param PropertyMetadata[] $propertyMetadatas
     * @return Property[]
     */
    private function createAutowiredProperties(array $propertyMetadatas) : array
    {
        $autowiredProperties = [];
        foreach ($propertyMetadatas as $propertyMetadata) {
            $propertyType = $propertyMetadata->getType();
            if (!$propertyType instanceof ObjectType) {
                throw new ShouldNotHappenException();
            }
            // create property
            $autowiredProperties[] = new Property(Modifiers::PRIVATE, [new PropertyItem($propertyMetadata->getName())], [], new FullyQualified($propertyType->getClassName()));
        }
        return $autowiredProperties;
    }
}
