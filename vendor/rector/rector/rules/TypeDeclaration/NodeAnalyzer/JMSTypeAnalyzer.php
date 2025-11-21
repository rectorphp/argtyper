<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Attribute;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\Rector\Doctrine\CodeQuality\Enum\CollectionMapping;
use Argtyper202511\Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Argtyper202511\Rector\Enum\ClassName;
use Argtyper202511\Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Util\StringUtils;
final class JMSTypeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttributeFinder
     */
    private $attributeFinder;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(AttributeFinder $attributeFinder, PhpAttributeAnalyzer $phpAttributeAnalyzer, ValueResolver $valueResolver)
    {
        $this->attributeFinder = $attributeFinder;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function hasAtLeastOneUntypedPropertyUsingJmsAttribute(Class_ $class): bool
    {
        foreach ($class->getProperties() as $property) {
            if ($property->type instanceof Node) {
                continue;
            }
            if ($this->attributeFinder->hasAttributeByClasses($property, [ClassName::JMS_TYPE])) {
                return \true;
            }
        }
        return \false;
    }
    public function hasPropertyJMSTypeAttribute(Property $property): bool
    {
        if (!$this->phpAttributeAnalyzer->hasPhpAttribute($property, ClassName::JMS_TYPE)) {
            return \false;
        }
        // most likely collection, not sole type
        return !$this->phpAttributeAnalyzer->hasPhpAttributes($property, array_merge(CollectionMapping::TO_MANY_CLASSES, CollectionMapping::TO_ONE_CLASSES));
    }
    public function resolveTypeAttributeValue(Property $property): ?string
    {
        $jmsTypeAttribute = $this->attributeFinder->findAttributeByClass($property, ClassName::JMS_TYPE);
        if (!$jmsTypeAttribute instanceof Attribute) {
            return null;
        }
        $typeValue = $this->valueResolver->getValue($jmsTypeAttribute->args[0]->value);
        if (!is_string($typeValue)) {
            return null;
        }
        if (StringUtils::isMatch($typeValue, '#DateTime\<(.*?)\>#')) {
            // special case for DateTime, which is not a scalar type
            return 'DateTime';
        }
        return $typeValue;
    }
}
