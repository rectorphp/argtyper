<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\Identical;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanOr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\Rector\NodeManipulator\BinaryOpManipulator;
use Argtyper202511\Rector\Php71\ValueObject\TwoNodeMatch;
use Argtyper202511\Rector\PhpParser\Node\AssignAndBinaryMap;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\SimplifyConditionsRector\SimplifyConditionsRectorTest
 */
final class SimplifyConditionsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\AssignAndBinaryMap
     */
    private $assignAndBinaryMap;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(AssignAndBinaryMap $assignAndBinaryMap, BinaryOpManipulator $binaryOpManipulator, ValueResolver $valueResolver)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
        $this->binaryOpManipulator = $binaryOpManipulator;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Simplify conditions', [new CodeSample("if (! (\$foo !== 'bar')) {...", "if (\$foo === 'bar') {...")]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [BooleanNot::class, Identical::class];
    }
    /**
     * @param BooleanNot|Identical $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof BooleanNot) {
            return $this->processBooleanNot($node);
        }
        return $this->processIdenticalAndNotIdentical($node);
    }
    private function processBooleanNot(BooleanNot $booleanNot): ?Node
    {
        if (!$booleanNot->expr instanceof BinaryOp) {
            return null;
        }
        if ($this->shouldSkip($booleanNot->expr)) {
            return null;
        }
        return $this->createInversedBooleanOp($booleanNot->expr);
    }
    private function processIdenticalAndNotIdentical(Identical $identical): ?Node
    {
        $twoNodeMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode($identical, static function (Node $node): bool {
            return $node instanceof Identical || $node instanceof NotIdentical;
        }, function (Node $node): bool {
            return $node instanceof Expr && $this->valueResolver->isTrueOrFalse($node);
        });
        if (!$twoNodeMatch instanceof TwoNodeMatch) {
            return $twoNodeMatch;
        }
        /** @var Identical|NotIdentical $firstExpr */
        $firstExpr = $twoNodeMatch->getFirstExpr();
        $otherExpr = $twoNodeMatch->getSecondExpr();
        if ($this->valueResolver->isFalse($otherExpr)) {
            return $this->createInversedBooleanOp($firstExpr);
        }
        return $firstExpr;
    }
    /**
     * Skip too nested binary || binary > binary combinations
     */
    private function shouldSkip(BinaryOp $binaryOp): bool
    {
        if ($binaryOp instanceof BooleanOr) {
            return \true;
        }
        if ($binaryOp->left instanceof BinaryOp) {
            return \true;
        }
        return $binaryOp->right instanceof BinaryOp;
    }
    private function createInversedBooleanOp(BinaryOp $binaryOp): ?BinaryOp
    {
        $inversedBinaryClass = $this->assignAndBinaryMap->getInversed($binaryOp);
        if ($inversedBinaryClass === null) {
            return null;
        }
        return new $inversedBinaryClass($binaryOp->left, $binaryOp->right);
    }
}
