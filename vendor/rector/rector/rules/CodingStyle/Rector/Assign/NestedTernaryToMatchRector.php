<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\Rector\Assign;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\Match_;
use Argtyper202511\PhpParser\Node\Expr\Ternary;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\MatchArm;
use Argtyper202511\Rector\CodingStyle\ValueObject\ConditionAndResult;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Assign\NestedTernaryToMatchRector\NestedTernaryToMatchRectorTest
 */
final class NestedTernaryToMatchRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert nested ternary expressions to match(true) statements', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function getValue($input)
    {
        return $input > 100 ? 'more than 100' : ($input > 5 ? 'more than 5' : 'less');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function getValue($input)
    {
        return match (true) {
            $input > 100 => 'more than 100',
            $input > 5 => 'more than 5',
            default => 'less',
        };
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(Node $node) : ?Assign
    {
        if (!$node->expr instanceof Ternary) {
            return null;
        }
        $ternary = $node->expr;
        // traverse nested ternaries to collect them all
        $currentTernary = $ternary;
        /** @var ConditionAndResult[] $conditionsAndResults */
        $conditionsAndResults = [];
        $defaultExpr = null;
        while ($currentTernary instanceof Ternary) {
            if (!$currentTernary->if instanceof Expr) {
                // short ternary, skip
                return null;
            }
            $conditionsAndResults[] = new ConditionAndResult($currentTernary->cond, $currentTernary->if);
            $currentTernary = $currentTernary->else;
            if (!$currentTernary instanceof Ternary) {
                $defaultExpr = $currentTernary;
            }
        }
        // nothing long enough
        if (\count($conditionsAndResults) < 2 || !$defaultExpr instanceof Expr) {
            return null;
        }
        $match = $this->createMatch($conditionsAndResults, $defaultExpr);
        $node->expr = $match;
        return $node;
    }
    /**
     * @param ConditionAndResult[] $conditionsAndResults
     */
    private function createMatch(array $conditionsAndResults, Expr $defaultExpr) : Match_
    {
        $singleVariableName = $this->matchAlwaysIdenticalVariableName($conditionsAndResults);
        if (\is_string($singleVariableName)) {
            $isVariableIdentical = \true;
            $match = new Match_(new Variable($singleVariableName));
        } else {
            $isVariableIdentical = \false;
            $match = new Match_($this->nodeFactory->createTrue());
        }
        foreach ($conditionsAndResults as $conditionAndResult) {
            $match->arms[] = new MatchArm([$isVariableIdentical ? $conditionAndResult->getIdenticalExpr() : $conditionAndResult->getConditionExpr()], $conditionAndResult->getResultExpr());
        }
        $match->arms[] = new MatchArm(null, $defaultExpr);
        return $match;
    }
    /**
     * @param ConditionAndResult[] $conditionsAndResults
     * @return mixed
     */
    private function matchAlwaysIdenticalVariableName(array $conditionsAndResults)
    {
        $identicalVariableNames = [];
        foreach ($conditionsAndResults as $conditionAndResult) {
            if (!$conditionAndResult->isIdenticalCompare()) {
                return null;
            }
            $variableName = $conditionAndResult->getIdenticalVariableName();
            if (!\is_string($variableName)) {
                return null;
            }
            $identicalVariableNames[] = $variableName;
        }
        $uniqueIdenticalVariableNames = \array_unique($identicalVariableNames);
        $uniqueIdenticalVariableNames = \array_values($uniqueIdenticalVariableNames);
        if (\count($uniqueIdenticalVariableNames) === 1) {
            return $uniqueIdenticalVariableNames[0];
        }
        return null;
    }
}
