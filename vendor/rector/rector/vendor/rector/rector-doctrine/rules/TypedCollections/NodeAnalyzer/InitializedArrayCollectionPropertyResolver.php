<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\Doctrine\Enum\DoctrineClass;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\ValueObject\MethodName;
final class InitializedArrayCollectionPropertyResolver
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
     * @return string[]
     */
    public function resolve(Class_ $class): array
    {
        $constructorClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof ClassMethod) {
            return [];
        }
        $initializedPropertyNames = [];
        foreach ((array) $constructorClassMethod->stmts as $constructorStmt) {
            if (!$constructorStmt instanceof Expression) {
                continue;
            }
            if (!$constructorStmt->expr instanceof Assign) {
                continue;
            }
            $assign = $constructorStmt->expr;
            if (!$this->isNewArrayCollection($assign->expr)) {
                continue;
            }
            if (!$assign->var instanceof PropertyFetch) {
                continue;
            }
            $propertyFetch = $assign->var;
            $initializedPropertyNames[] = (string) $this->nodeNameResolver->getName($propertyFetch->name);
        }
        return $initializedPropertyNames;
    }
    private function isNewArrayCollection(Expr $expr): bool
    {
        if (!$expr instanceof New_) {
            return \false;
        }
        return $this->nodeNameResolver->isName($expr->class, DoctrineClass::ARRAY_COLLECTION);
    }
}
