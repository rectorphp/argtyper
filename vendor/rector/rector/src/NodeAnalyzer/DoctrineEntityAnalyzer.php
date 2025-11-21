<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
/**
 * @api used in doctrine
 */
final class DoctrineEntityAnalyzer
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var string[]
     */
    private const DOCTRINE_MAPPING_CLASSES = ['Argtyper202511\Doctrine\ORM\Mapping\Entity', 'Argtyper202511\Doctrine\ORM\Mapping\Embeddable', 'Argtyper202511\Doctrine\ODM\MongoDB\Mapping\Annotations\Document', 'Argtyper202511\Doctrine\ODM\MongoDB\Mapping\Annotations\EmbeddedDocument'];
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function hasClassAnnotation(Class_ $class): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($class);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return \false;
        }
        return $phpDocInfo->hasByAnnotationClasses(self::DOCTRINE_MAPPING_CLASSES);
    }
    public function hasClassReflectionAttribute(ClassReflection $classReflection): bool
    {
        /** @var ReflectionClass $nativeReflectionClass */
        $nativeReflectionClass = $classReflection->getNativeReflection();
        // skip early in case of no attributes at all
        if ((method_exists($nativeReflectionClass, 'getAttributes') ? $nativeReflectionClass->getAttributes() : []) === []) {
            return \false;
        }
        foreach (self::DOCTRINE_MAPPING_CLASSES as $doctrineMappingClass) {
            // skip entities
            if ((method_exists($nativeReflectionClass, 'getAttributes') ? $nativeReflectionClass->getAttributes($doctrineMappingClass) : []) !== []) {
                return \true;
            }
        }
        return \false;
    }
}
