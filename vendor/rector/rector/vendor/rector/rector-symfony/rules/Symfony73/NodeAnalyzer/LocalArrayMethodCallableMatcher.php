<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Symfony73\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
final class LocalArrayMethodCallableMatcher
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function match(Expr $expr, ObjectType $objectType): ?string
    {
        if ($expr instanceof MethodCall) {
            if (!$expr->name instanceof Identifier) {
                return null;
            }
            if (!$this->nodeTypeResolver->isObjectType($expr->var, $objectType)) {
                return null;
            }
            return $expr->name->toString();
        }
        if ($expr instanceof Array_) {
            if (!$this->nodeTypeResolver->isObjectType($expr->items[0]->value, $objectType)) {
                return null;
            }
            $secondItem = $expr->items[1];
            if (!$secondItem->value instanceof String_) {
                return null;
            }
            return $secondItem->value->value;
        }
        return null;
    }
}
