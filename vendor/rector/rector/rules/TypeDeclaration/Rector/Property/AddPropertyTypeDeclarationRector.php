<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\Property;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddPropertyTypeDeclaration;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector\AddPropertyTypeDeclarationRectorTest
 */
final class AddPropertyTypeDeclarationRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var AddPropertyTypeDeclaration[]
     */
    private $addPropertyTypeDeclarations = [];
    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add type to property by added rules, mostly public/property by parent type', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass extends ParentClass
{
    public $name;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass extends ParentClass
{
    public string $name;
}
CODE_SAMPLE
, [new AddPropertyTypeDeclaration('ParentClass', 'name', new StringType())])]);
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
    public function refactor(Node $node): ?Node
    {
        // type is already known
        if ($node->type !== null) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        foreach ($this->addPropertyTypeDeclarations as $addPropertyTypeDeclaration) {
            if (!$this->isClassReflectionType($classReflection, $addPropertyTypeDeclaration->getClass())) {
                continue;
            }
            if (!$this->isName($node, $addPropertyTypeDeclaration->getPropertyName())) {
                continue;
            }
            $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($addPropertyTypeDeclaration->getType(), TypeKind::PROPERTY);
            if (!$typeNode instanceof Node) {
                // invalid configuration
                throw new ShouldNotHappenException();
            }
            $node->type = $typeNode;
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, AddPropertyTypeDeclaration::class);
        $this->addPropertyTypeDeclarations = $configuration;
    }
    private function isClassReflectionType(ClassReflection $classReflection, string $type): bool
    {
        if ($classReflection->hasTraitUse($type)) {
            return \true;
        }
        return $classReflection->is($type);
    }
}
