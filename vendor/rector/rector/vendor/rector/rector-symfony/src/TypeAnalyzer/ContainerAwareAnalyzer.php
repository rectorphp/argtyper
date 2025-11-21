<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\TypeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\Symfony\Enum\SymfonyClass;
final class ContainerAwareAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var ObjectType[]
     */
    private $getMethodAwareObjectTypes = [];
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->getMethodAwareObjectTypes = [new ObjectType(SymfonyClass::ABSTRACT_CONTROLLER), new ObjectType(SymfonyClass::CONTROLLER), new ObjectType(SymfonyClass::CONTROLLER_TRAIT)];
    }
    public function isGetMethodAwareType(Expr $expr) : bool
    {
        return $this->nodeTypeResolver->isObjectTypes($expr, $this->getMethodAwareObjectTypes);
    }
}
