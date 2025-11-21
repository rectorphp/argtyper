<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Instanceof_\Rector\Ternary;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\Instanceof_;
use Argtyper202511\PhpParser\Node\Expr\Ternary;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Instanceof_\Rector\Ternary\FlipNegatedTernaryInstanceofRector\FlipNegatedTernaryInstanceofRectorTest
 */
final class FlipNegatedTernaryInstanceofRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Flip negated ternary of `instanceof` to direct use of object', [new CodeSample('echo ! $object instanceof Product ? null : $object->getPrice();', 'echo $object instanceof Product ? $object->getPrice() : null;')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->if instanceof Expr) {
            return null;
        }
        if (!$node->cond instanceof BooleanNot) {
            return null;
        }
        $booleanNot = $node->cond;
        if (!$booleanNot->expr instanceof Instanceof_) {
            return null;
        }
        $node->cond = $booleanNot->expr;
        // flip if and else
        [$node->if, $node->else] = [$node->else, $node->if];
        return $node;
    }
}
