<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\NullableType;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PhpParser\Node\UnionType;
use Argtyper202511\Rector\Doctrine\Enum\DoctrineClass;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
final class CollectionPropertyDetector
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function detect(Property $property) : bool
    {
        if (!$property->type instanceof Node) {
            return \false;
        }
        // 1. direct type
        if ($this->nodeNameResolver->isName($property->type, DoctrineClass::COLLECTION)) {
            return \true;
        }
        // 2. union type
        if ($property->type instanceof UnionType) {
            $unionType = $property->type;
            foreach ($unionType->types as $unionedType) {
                if ($this->nodeNameResolver->isName($unionedType, DoctrineClass::COLLECTION)) {
                    return \true;
                }
            }
        }
        // 3. nullable type
        if ($property->type instanceof NullableType) {
            $directType = $property->type->type;
            if ($this->nodeNameResolver->isName($directType, DoctrineClass::COLLECTION)) {
                return \true;
            }
        }
        return \false;
    }
}
