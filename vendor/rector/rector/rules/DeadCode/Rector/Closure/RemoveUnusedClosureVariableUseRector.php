<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\Closure;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Closure\RemoveUnusedClosureVariableUseRector\RemoveUnusedClosureVariableUseRectorTest
 */
final class RemoveUnusedClosureVariableUseRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer
     */
    private $exprUsedInNodeAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->exprUsedInNodeAnalyzer = $exprUsedInNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused variable in use() of closure', [new CodeSample(<<<'CODE_SAMPLE'
$var = 1;

$closure = function() use ($var) {
    echo 'Hello World';
};

CODE_SAMPLE
, <<<'CODE_SAMPLE'
$var = 1;
$closure = function() {
    echo 'Hello World';
};

CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->uses === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->uses as $key => $useVariable) {
            $useVariableName = $this->getName($useVariable->var);
            if (!\is_string($useVariableName)) {
                continue;
            }
            $isUseUsed = (bool) $this->betterNodeFinder->findFirst($node->stmts, function (Node $subNode) use($useVariable) : bool {
                return $this->exprUsedInNodeAnalyzer->isUsed($subNode, $useVariable->var);
            });
            if ($isUseUsed) {
                continue;
            }
            unset($node->uses[$key]);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
