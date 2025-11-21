<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Renaming\Rector\PropertyFetch;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticPropertyFetch;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt\ClassLike;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PhpParser\Node\VarLikeIdentifier;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Renaming\ValueObject\RenameProperty;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\RenamePropertyRectorTest
 */
final class RenamePropertyRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var RenameProperty[]
     */
    private $renamedProperties = [];
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace defined old properties by new ones', [new ConfiguredCodeSample('$someObject->someOldProperty;', '$someObject->someNewProperty;', [new RenameProperty('SomeClass', 'someOldProperty', 'someNewProperty')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [PropertyFetch::class, StaticPropertyFetch::class, ClassLike::class];
    }
    /**
     * @param PropertyFetch|StaticPropertyFetch|ClassLike $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassLike) {
            $this->hasChanged = \false;
            foreach ($this->renamedProperties as $renamedProperty) {
                $this->renameProperty($node, $renamedProperty);
            }
            if ($this->hasChanged) {
                return $node;
            }
            return null;
        }
        return $this->refactorPropertyFetch($node);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, RenameProperty::class);
        $this->renamedProperties = $configuration;
    }
    private function renameProperty(ClassLike $classLike, RenameProperty $renameProperty): void
    {
        $classLikeName = (string) $this->getName($classLike);
        $renamePropertyObjectType = $renameProperty->getObjectType();
        $className = $renamePropertyObjectType->getClassName();
        $classLikeNameObjectType = new ObjectType($classLikeName);
        $classNameObjectType = new ObjectType($className);
        $isSuperType = $classNameObjectType->isSuperTypeOf($classLikeNameObjectType)->yes();
        if ($classLikeName !== $className && !$isSuperType) {
            return;
        }
        $property = $classLike->getProperty($renameProperty->getOldProperty());
        if (!$property instanceof Property) {
            return;
        }
        $newProperty = $renameProperty->getNewProperty();
        $targetNewProperty = $classLike->getProperty($newProperty);
        if ($targetNewProperty instanceof Property) {
            return;
        }
        $this->hasChanged = \true;
        $property->props[0]->name = new VarLikeIdentifier($newProperty);
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
     * @return null|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch
     */
    private function refactorPropertyFetch($propertyFetch)
    {
        foreach ($this->renamedProperties as $renamedProperty) {
            $oldProperty = $renamedProperty->getOldProperty();
            if (!$this->isName($propertyFetch, $oldProperty)) {
                continue;
            }
            $varPropertyFetch = $propertyFetch instanceof PropertyFetch ? $propertyFetch->var : $propertyFetch->class;
            if (!$this->isObjectType($varPropertyFetch, $renamedProperty->getObjectType())) {
                continue;
            }
            $propertyFetch->name = $propertyFetch instanceof PropertyFetch ? new Identifier($renamedProperty->getNewProperty()) : new VarLikeIdentifier($renamedProperty->getNewProperty());
            return $propertyFetch;
        }
        return null;
    }
}
