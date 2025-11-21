<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Include_;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\Rector\NodeAnalyzer\CompactFuncCallAnalyzer;
final class ExprUsedInNodeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer
     */
    private $usedVariableNameAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\CompactFuncCallAnalyzer
     */
    private $compactFuncCallAnalyzer;
    public function __construct(\Argtyper202511\Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer $usedVariableNameAnalyzer, CompactFuncCallAnalyzer $compactFuncCallAnalyzer)
    {
        $this->usedVariableNameAnalyzer = $usedVariableNameAnalyzer;
        $this->compactFuncCallAnalyzer = $compactFuncCallAnalyzer;
    }
    public function isUsed(Node $node, Variable $variable) : bool
    {
        if ($node instanceof Include_) {
            return \true;
        }
        // variable as variable variable need mark as used
        if ($node instanceof Variable && $node->name instanceof Expr) {
            return \true;
        }
        if ($node instanceof FuncCall) {
            return $this->compactFuncCallAnalyzer->isInCompact($node, $variable);
        }
        return $this->usedVariableNameAnalyzer->isVariableNamed($node, $variable);
    }
}
