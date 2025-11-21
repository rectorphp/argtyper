<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\NodeAnalyser;

use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
final class SetUpAssignedMockTypesResolver
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
     * @return array<string, string>
     */
    public function resolveFromClass(Class_ $class) : array
    {
        $setUpClassMethod = $class->getMethod(MethodName::SET_UP);
        if (!$setUpClassMethod instanceof ClassMethod) {
            return [];
        }
        $propertyNameToMockedTypes = [];
        foreach ((array) $setUpClassMethod->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->expr instanceof MethodCall) {
                continue;
            }
            if (!$this->nodeNameResolver->isNames($assign->expr->name, ['createMock', 'getMockBuilder'])) {
                continue;
            }
            if (!$assign->var instanceof PropertyFetch && !$assign->var instanceof Variable) {
                continue;
            }
            $mockedClassNameExpr = $assign->expr->getArgs()[0]->value;
            if (!$mockedClassNameExpr instanceof ClassConstFetch) {
                continue;
            }
            $propertyOrVariableName = $this->resolvePropertyOrVariableName($assign->var);
            $mockedClass = $this->nodeNameResolver->getName($mockedClassNameExpr->class);
            Assert::string($mockedClass);
            $propertyNameToMockedTypes[$propertyOrVariableName] = $mockedClass;
        }
        return $propertyNameToMockedTypes;
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\Variable $propertyFetchOrVariable
     */
    private function resolvePropertyOrVariableName($propertyFetchOrVariable) : ?string
    {
        if ($propertyFetchOrVariable instanceof Variable) {
            return $this->nodeNameResolver->getName($propertyFetchOrVariable);
        }
        return $this->nodeNameResolver->getName($propertyFetchOrVariable->name);
    }
}
