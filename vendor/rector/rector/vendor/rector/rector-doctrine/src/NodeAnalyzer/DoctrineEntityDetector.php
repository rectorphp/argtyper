<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\NodeAnalyzer\DoctrineEntityAnalyzer;
/**
 * @api Part of external API
 */
final class DoctrineEntityDetector
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\DoctrineEntityAnalyzer
     */
    private $doctrineEntityAnalyzer;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(DoctrineEntityAnalyzer $doctrineEntityAnalyzer, ReflectionProvider $reflectionProvider)
    {
        $this->doctrineEntityAnalyzer = $doctrineEntityAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function detect(Class_ $class): bool
    {
        // A. check annotations
        if ($this->doctrineEntityAnalyzer->hasClassAnnotation($class)) {
            return \true;
        }
        if (!$class->namespacedName instanceof Name) {
            return \false;
        }
        $className = $class->namespacedName->toString();
        // B. check attributes
        $classReflection = $this->reflectionProvider->getClass($className);
        return $this->doctrineEntityAnalyzer->hasClassReflectionAttribute($classReflection);
    }
}
