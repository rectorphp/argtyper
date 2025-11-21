<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\NodeFactory;

use Argtyper202511\PhpParser\BuilderFactory;
use Argtyper202511\PhpParser\BuilderHelpers;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Attribute;
use Argtyper202511\PhpParser\Node\AttributeGroup;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
final class AttributeFactory
{
    /**
     * @param mixed $expr
     */
    public static function createNamedArg($expr, string $name): Arg
    {
        if (!$expr instanceof Expr) {
            $expr = BuilderHelpers::normalizeValue($expr);
        }
        return new Arg($expr, \false, \false, [], new Identifier($name));
    }
    /**
     * @param array<mixed|Arg> $values
     */
    public static function createGroup(string $className, array $values = []): AttributeGroup
    {
        $builderFactory = new BuilderFactory();
        $args = $builderFactory->args($values);
        $attribute = new Attribute(new FullyQualified($className), $args);
        return new AttributeGroup([$attribute]);
    }
}
