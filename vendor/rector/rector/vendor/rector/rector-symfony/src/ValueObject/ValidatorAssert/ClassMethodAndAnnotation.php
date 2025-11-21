<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\ValueObject\ValidatorAssert;

use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
final class ClassMethodAndAnnotation
{
    /**
     * @var string[]
     * @readonly
     */
    private $possibleMethodNames;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
     */
    private $doctrineAnnotationTagValueNode;
    /**
     * @param string[] $possibleMethodNames
     */
    public function __construct(array $possibleMethodNames, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode)
    {
        $this->possibleMethodNames = $possibleMethodNames;
        $this->doctrineAnnotationTagValueNode = $doctrineAnnotationTagValueNode;
    }
    /**
     * @return string[]
     */
    public function getPossibleMethodNames() : array
    {
        return $this->possibleMethodNames;
    }
    public function getDoctrineAnnotationTagValueNode() : DoctrineAnnotationTagValueNode
    {
        return $this->doctrineAnnotationTagValueNode;
    }
}
