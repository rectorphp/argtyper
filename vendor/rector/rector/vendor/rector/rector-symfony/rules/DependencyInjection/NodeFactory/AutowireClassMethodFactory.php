<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\DependencyInjection\NodeFactory;

use Argtyper202511\PhpParser\Comment\Doc;
use Argtyper202511\PhpParser\Modifiers;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Trait_;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PostRector\ValueObject\PropertyMetadata;
final class AutowireClassMethodFactory
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
    /**
     * @param PropertyMetadata[] $propertyMetadatas
     */
    public function create(Trait_ $trait, array $propertyMetadatas) : ClassMethod
    {
        $traitName = $this->nodeNameResolver->getShortName($trait);
        $autowireClassMethod = new ClassMethod('autowire' . $traitName);
        $autowireClassMethod->flags |= Modifiers::PUBLIC;
        $autowireClassMethod->returnType = new Identifier('void');
        $autowireClassMethod->setDocComment(new Doc("/**\n * @required\n */"));
        foreach ($propertyMetadatas as $propertyMetadata) {
            $param = $this->createAutowiredParam($propertyMetadata);
            $autowireClassMethod->params[] = $param;
            $createPropertyAssign = new Assign(new PropertyFetch(new Variable('this'), new Identifier($propertyMetadata->getName())), new Variable($propertyMetadata->getName()));
            $autowireClassMethod->stmts[] = new Expression($createPropertyAssign);
        }
        return $autowireClassMethod;
    }
    private function createAutowiredParam(PropertyMetadata $propertyMetadata) : Param
    {
        $param = new Param(new Variable($propertyMetadata->getName()));
        $objectType = $propertyMetadata->getType();
        if (!$objectType instanceof ObjectType) {
            throw new ShouldNotHappenException();
        }
        $param->type = new FullyQualified($objectType->getClassName());
        return $param;
    }
}
