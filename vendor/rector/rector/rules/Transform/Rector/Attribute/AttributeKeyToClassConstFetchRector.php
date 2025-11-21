<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\Rector\Attribute;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\AttributeGroup;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\Stmt\Interface_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Transform\ValueObject\AttributeKeyToClassConstFetch;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @api used in rector-doctrine
 * @see \Rector\Tests\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector\AttributeKeyToClassConstFetchRectorTest
 */
final class AttributeKeyToClassConstFetchRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var AttributeKeyToClassConstFetch[]
     */
    private $attributeKeysToClassConstFetches = [];
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace key value on specific attribute to class constant', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping\Column;

class SomeClass
{
    #[Column(type: "string")]
    public $name;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping\Column;
use Doctrine\DBAL\Types\Types;

class SomeClass
{
    #[Column(type: Types::STRING)]
    public $name;
}
CODE_SAMPLE
, [new AttributeKeyToClassConstFetch('Argtyper202511\Doctrine\ORM\Mapping\Column', 'type', 'Argtyper202511\Doctrine\DBAL\Types\Types', ['string' => 'STRING'])])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, Property::class, Param::class, ClassMethod::class, Function_::class, Closure::class, ArrowFunction::class, Interface_::class];
    }
    /**
     * @param Class_|Property|Param|ClassMethod|Function_|Closure|ArrowFunction|Interface_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->attrGroups === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($this->attributeKeysToClassConstFetches as $attributeKeyToClassConstFetch) {
            foreach ($node->attrGroups as $attrGroup) {
                if ($this->processToClassConstFetch($attrGroup, $attributeKeyToClassConstFetch)) {
                    $hasChanged = \true;
                }
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, AttributeKeyToClassConstFetch::class);
        $this->attributeKeysToClassConstFetches = $configuration;
    }
    private function processToClassConstFetch(AttributeGroup $attributeGroup, AttributeKeyToClassConstFetch $attributeKeyToClassConstFetch): bool
    {
        $hasChanged = \false;
        foreach ($attributeGroup->attrs as $attribute) {
            if (!$this->isName($attribute->name, $attributeKeyToClassConstFetch->getAttributeClass())) {
                continue;
            }
            foreach ($attribute->args as $arg) {
                $argName = $arg->name;
                if (!$argName instanceof Identifier) {
                    continue;
                }
                if (!$this->isName($argName, $attributeKeyToClassConstFetch->getAttributeKey())) {
                    continue;
                }
                if ($this->processArg($arg, $attributeKeyToClassConstFetch)) {
                    $hasChanged = \true;
                }
            }
        }
        return $hasChanged;
    }
    private function processArg(Arg $arg, AttributeKeyToClassConstFetch $attributeKeyToClassConstFetch): bool
    {
        $value = $this->valueResolver->getValue($arg->value);
        $constName = $attributeKeyToClassConstFetch->getValuesToConstantsMap()[$value] ?? null;
        if ($constName === null) {
            return \false;
        }
        $classConstFetch = $this->nodeFactory->createClassConstFetch($attributeKeyToClassConstFetch->getConstantClass(), $constName);
        if ($arg->value instanceof ClassConstFetch && $this->getName($arg->value) === $this->getName($classConstFetch)) {
            return \false;
        }
        $arg->value = $classConstFetch;
        return \true;
    }
}
