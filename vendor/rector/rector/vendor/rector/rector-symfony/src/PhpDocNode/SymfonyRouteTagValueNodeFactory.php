<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\PhpDocNode;

use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Argtyper202511\Rector\Symfony\Enum\SymfonyAnnotation;
final class SymfonyRouteTagValueNodeFactory
{
    /**
     * @param ArrayItemNode[] $arrayItemNodes
     */
    public function createFromItems(array $arrayItemNodes) : DoctrineAnnotationTagValueNode
    {
        $identifierTypeNode = new IdentifierTypeNode(SymfonyAnnotation::ROUTE);
        return new DoctrineAnnotationTagValueNode($identifierTypeNode, null, $arrayItemNodes, 'path');
    }
}
