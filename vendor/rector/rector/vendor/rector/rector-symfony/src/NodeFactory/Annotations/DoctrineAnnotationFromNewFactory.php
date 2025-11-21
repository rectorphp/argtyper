<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\NodeFactory\Annotations;

use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\Type\ShortenedIdentifierTypeNode;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
final class DoctrineAnnotationFromNewFactory
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\Annotations\DoctrineAnnotationKeyToValuesResolver
     */
    private $doctrineAnnotationKeyToValuesResolver;
    public function __construct(\Argtyper202511\Rector\Symfony\NodeFactory\Annotations\DoctrineAnnotationKeyToValuesResolver $doctrineAnnotationKeyToValuesResolver)
    {
        $this->doctrineAnnotationKeyToValuesResolver = $doctrineAnnotationKeyToValuesResolver;
    }
    public function create(New_ $new) : DoctrineAnnotationTagValueNode
    {
        $annotationName = $this->resolveAnnotationName($new);
        $newArgs = $new->getArgs();
        if (isset($newArgs[0])) {
            $firstAnnotationArg = $newArgs[0]->value;
            $annotationKeyToValues = $this->doctrineAnnotationKeyToValuesResolver->resolveFromExpr($firstAnnotationArg);
        } else {
            $annotationKeyToValues = [];
        }
        return new DoctrineAnnotationTagValueNode(new ShortenedIdentifierTypeNode($annotationName), null, $annotationKeyToValues);
    }
    private function resolveAnnotationName(New_ $new) : string
    {
        $className = $new->class;
        $originalName = $className->getAttribute(AttributeKey::ORIGINAL_NAME);
        if (!$originalName instanceof Name) {
            throw new ShouldNotHappenException();
        }
        return $originalName->toString();
    }
}
