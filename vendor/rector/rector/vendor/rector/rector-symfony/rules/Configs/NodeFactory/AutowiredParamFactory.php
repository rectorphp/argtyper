<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\NodeFactory;

use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Attribute;
use Argtyper202511\PhpParser\Node\AttributeGroup;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Rector\Symfony\Enum\SymfonyAttribute;
final class AutowiredParamFactory
{
    public function create(string $variableName, String_ $string): Param
    {
        $parameterParam = new Param(new Variable($variableName));
        $parameterParam->attrGroups[] = new AttributeGroup([$this->createAutowireAttribute($string, 'param')]);
        return $parameterParam;
    }
    private function createAutowireAttribute(String_ $string, string $argName): Attribute
    {
        $args = [new Arg($string, \false, \false, [], new Identifier($argName))];
        return new Attribute(new FullyQualified(SymfonyAttribute::AUTOWIRE), $args);
    }
}
