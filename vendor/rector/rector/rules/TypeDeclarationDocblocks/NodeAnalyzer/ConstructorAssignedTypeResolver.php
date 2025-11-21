<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclarationDocblocks\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\ValueObject\MethodName;
final class ConstructorAssignedTypeResolver
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function resolve(Class_ $class, string $propertyName) : ?Type
    {
        $constructorClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof ClassMethod) {
            return null;
        }
        if ($constructorClassMethod->stmts === null) {
            return null;
        }
        $assigns = $this->betterNodeFinder->findInstancesOfScoped($constructorClassMethod->stmts, Assign::class);
        foreach ($assigns as $assign) {
            if (!$assign->var instanceof PropertyFetch) {
                continue;
            }
            $propertyFetch = $assign->var;
            if (!$this->nodeNameResolver->isName($propertyFetch->var, 'this')) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($propertyFetch->name, $propertyName)) {
                continue;
            }
            return $this->nodeTypeResolver->getType($assign->expr);
        }
        return null;
    }
}
