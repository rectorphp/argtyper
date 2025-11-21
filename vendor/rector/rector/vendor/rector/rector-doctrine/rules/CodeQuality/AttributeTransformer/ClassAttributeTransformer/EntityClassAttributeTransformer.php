<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality\AttributeTransformer\ClassAttributeTransformer;

use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\Rector\Doctrine\CodeQuality\Contract\ClassAttributeTransformerInterface;
use Argtyper202511\Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Argtyper202511\Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Argtyper202511\Rector\Doctrine\Enum\MappingClass;
final class EntityClassAttributeTransformer implements ClassAttributeTransformerInterface
{
    /**
     * @var string
     */
    private const REPOSITORY_CLASS_KEY = 'repositoryClass';
    public function transform(EntityMapping $entityMapping, Class_ $class): bool
    {
        $classMapping = $entityMapping->getClassMapping();
        $type = $classMapping['type'] ?? null;
        if ($type !== 'entity') {
            return \false;
        }
        $args = [];
        $repositoryClass = $classMapping[self::REPOSITORY_CLASS_KEY] ?? null;
        if ($repositoryClass) {
            $repositoryClassConstFetch = new ClassConstFetch(new FullyQualified($repositoryClass), 'class');
            $args[] = AttributeFactory::createNamedArg($repositoryClassConstFetch, self::REPOSITORY_CLASS_KEY);
        }
        $class->attrGroups[] = AttributeFactory::createGroup($this->getClassName(), $args);
        return \true;
    }
    public function getClassName(): string
    {
        return MappingClass::ENTITY;
    }
}
