<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Naming\Naming\PropertyNaming;
use Argtyper202511\Rector\NodeManipulator\ClassDependencyManipulator;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpParser\Node\NodeFactory;
use Argtyper202511\Rector\PostRector\ValueObject\PropertyMetadata;
use Argtyper202511\Rector\Transform\NodeFactory\PropertyFetchFactory;
use Argtyper202511\Rector\Transform\NodeTypeAnalyzer\TypeProvidingExprFromClassResolver;
final class FuncCallStaticCallToMethodCallAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Transform\NodeTypeAnalyzer\TypeProvidingExprFromClassResolver
     */
    private $typeProvidingExprFromClassResolver;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\Transform\NodeFactory\PropertyFetchFactory
     */
    private $propertyFetchFactory;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\ClassDependencyManipulator
     */
    private $classDependencyManipulator;
    public function __construct(TypeProvidingExprFromClassResolver $typeProvidingExprFromClassResolver, PropertyNaming $propertyNaming, NodeNameResolver $nodeNameResolver, NodeFactory $nodeFactory, PropertyFetchFactory $propertyFetchFactory, ClassDependencyManipulator $classDependencyManipulator)
    {
        $this->typeProvidingExprFromClassResolver = $typeProvidingExprFromClassResolver;
        $this->propertyNaming = $propertyNaming;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
        $this->propertyFetchFactory = $propertyFetchFactory;
        $this->classDependencyManipulator = $classDependencyManipulator;
    }
    /**
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\Variable
     */
    public function matchTypeProvidingExpr(Class_ $class, ClassMethod $classMethod, ObjectType $objectType)
    {
        $expr = $this->typeProvidingExprFromClassResolver->resolveTypeProvidingExprFromClass($class, $classMethod, $objectType);
        if ($expr instanceof Expr) {
            if ($expr instanceof Variable) {
                $this->addClassMethodParamForVariable($expr, $objectType, $classMethod);
            }
            return $expr;
        }
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $this->classDependencyManipulator->addConstructorDependency($class, new PropertyMetadata($propertyName, $objectType));
        return $this->propertyFetchFactory->createFromType($objectType);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function addClassMethodParamForVariable(Variable $variable, ObjectType $objectType, $functionLike): void
    {
        /** @var string $variableName */
        $variableName = $this->nodeNameResolver->getName($variable);
        // add variable to __construct as dependency
        $functionLike->params[] = $this->nodeFactory->createParamFromNameAndType($variableName, $objectType);
    }
}
