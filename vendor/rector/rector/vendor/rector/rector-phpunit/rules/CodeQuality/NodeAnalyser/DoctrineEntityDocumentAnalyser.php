<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\NodeAnalyser;

use Argtyper202511\PHPStan\PhpDoc\ResolvedPhpDocBlock;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
final class DoctrineEntityDocumentAnalyser
{
    /**
     * @var string[]
     */
    private const ENTITY_DOCBLOCK_MARKERS = ['@Document', '@ORM\\Document', '@Entity', '@ORM\\Entity'];
    public function isEntityClass(ClassReflection $classReflection) : bool
    {
        $resolvedPhpDocBlock = $classReflection->getResolvedPhpDoc();
        if (!$resolvedPhpDocBlock instanceof ResolvedPhpDocBlock) {
            return \false;
        }
        foreach (self::ENTITY_DOCBLOCK_MARKERS as $entityDocBlockMarkers) {
            if (\strpos($resolvedPhpDocBlock->getPhpDocString(), $entityDocBlockMarkers) !== \false) {
                return \true;
            }
        }
        // @todo apply attributes as well
        return \false;
    }
}
