<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Matcher;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\Rector\NodeAnalyzer\PropertyFetchAnalyzer;
final class PropertyAssignMatcher
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    public function __construct(PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    /**
     * Covers:
     * - $this->propertyName = $expr;
     * - $this->propertyName[] = $expr;
     */
    public function matchPropertyAssignExpr(Assign $assign, string $propertyName) : ?Expr
    {
        $assignVar = $assign->var;
        if ($this->propertyFetchAnalyzer->isLocalPropertyFetchName($assignVar, $propertyName)) {
            return $assign->expr;
        }
        if (!$assignVar instanceof ArrayDimFetch) {
            return null;
        }
        if ($this->propertyFetchAnalyzer->isLocalPropertyFetchName($assignVar->var, $propertyName)) {
            return $assign->expr;
        }
        return null;
    }
}
