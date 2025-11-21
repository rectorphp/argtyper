<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\Rector\Doctrine\Enum\DoctrineClass;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
final class FreshArrayCollectionAnalyzer
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
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function doesReturnNewArrayCollectionVariable(ClassMethod $classMethod) : bool
    {
        $newArrayCollectionVariableName = $this->resolveNewArrayCollectionVariableName($classMethod);
        if ($newArrayCollectionVariableName === null) {
            return \false;
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($classMethod);
        if (\count($returns) !== 1) {
            return \false;
        }
        // the exact variable name must be returned
        $soleReturn = $returns[0];
        if (!$soleReturn->expr instanceof Variable) {
            return \false;
        }
        return $this->nodeNameResolver->isName($soleReturn->expr, $newArrayCollectionVariableName);
    }
    private function resolveNewArrayCollectionVariableName(ClassMethod $classMethod) : ?string
    {
        $assigns = $this->betterNodeFinder->findInstancesOfScoped([$classMethod], Assign::class);
        foreach ($assigns as $assign) {
            if (!$assign->expr instanceof New_) {
                continue;
            }
            $new = $assign->expr;
            if (!$this->nodeNameResolver->isName($new->class, DoctrineClass::ARRAY_COLLECTION)) {
                continue;
            }
            // detect variable name
            if (!$assign->var instanceof Variable) {
                continue;
            }
            $variable = $assign->var;
            return $this->nodeNameResolver->getName($variable);
        }
        return null;
    }
}
