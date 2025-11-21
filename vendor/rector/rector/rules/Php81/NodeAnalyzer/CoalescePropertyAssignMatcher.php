<?php

declare (strict_types=1);
namespace Rector\Php81\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Coalesce;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Rector\NodeNameResolver\NodeNameResolver;
final class CoalescePropertyAssignMatcher
{
    /**
     * @readonly
     * @var \Rector\Php81\NodeAnalyzer\ComplexNewAnalyzer
     */
    private $complexNewAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Php81\NodeAnalyzer\ComplexNewAnalyzer $complexNewAnalyzer, NodeNameResolver $nodeNameResolver)
    {
        $this->complexNewAnalyzer = $complexNewAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * Matches
     *
     * $this->value = $param ?? 'default';
     */
    public function matchCoalesceAssignsToLocalPropertyNamed(Stmt $stmt, string $propertyName): ?Coalesce
    {
        if (!$stmt instanceof Expression) {
            return null;
        }
        if (!$stmt->expr instanceof Assign) {
            return null;
        }
        $assign = $stmt->expr;
        if (!$assign->expr instanceof Coalesce) {
            return null;
        }
        $coalesce = $assign->expr;
        if (!$coalesce->right instanceof New_) {
            return null;
        }
        if ($this->complexNewAnalyzer->isDynamic($coalesce->right)) {
            return null;
        }
        if (!$this->isLocalPropertyFetchNamed($assign->var, $propertyName)) {
            return null;
        }
        return $assign->expr;
    }
    private function isLocalPropertyFetchNamed(Expr $expr, string $propertyName): bool
    {
        if (!$expr instanceof PropertyFetch) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($expr->var, 'this')) {
            return \false;
        }
        return $this->nodeNameResolver->isName($expr->name, $propertyName);
    }
}
