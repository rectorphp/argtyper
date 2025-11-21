<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality\AttributeTransformer\ClassAttributeTransformer;

use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\Rector\Doctrine\CodeQuality\Contract\ClassAttributeTransformerInterface;
use Argtyper202511\Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Argtyper202511\Rector\Doctrine\CodeQuality\Utils\CaseStringHelper;
use Argtyper202511\Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Argtyper202511\Rector\Doctrine\Enum\MappingClass;
use Argtyper202511\Rector\PhpParser\Node\NodeFactory;
final class SoftDeletableClassAttributeTransformer implements ClassAttributeTransformerInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    public function transform(EntityMapping $entityMapping, Class_ $class): bool
    {
        $classMapping = $entityMapping->getClassMapping();
        $softDeletableMapping = $classMapping['gedmo']['soft_deleteable'] ?? null;
        if (!is_array($softDeletableMapping)) {
            return \false;
        }
        $args = $this->nodeFactory->createArgs($softDeletableMapping);
        foreach ($args as $arg) {
            if (!$arg->name instanceof Identifier) {
                continue;
            }
            $arg->name = new Identifier(CaseStringHelper::camelCase($arg->name->toString()));
        }
        $class->attrGroups[] = AttributeFactory::createGroup($this->getClassName(), $args);
        return \true;
    }
    public function getClassName(): string
    {
        return MappingClass::GEDMO_SOFT_DELETEABLE;
    }
}
