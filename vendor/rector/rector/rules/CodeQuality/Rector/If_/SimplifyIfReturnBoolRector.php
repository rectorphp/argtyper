<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\If_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Equal;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotEqual;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Stmt\Else_;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\Rector\BetterPhpDocParser\Comment\CommentsMerger;
use Argtyper202511\Rector\CodeQuality\NodeManipulator\ExprBoolCaster;
use Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\PhpParser\Printer\BetterStandardPrinter;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector\SimplifyIfReturnBoolRectorTest
 */
final class SimplifyIfReturnBoolRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Comment\CommentsMerger
     */
    private $commentsMerger;
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeManipulator\ExprBoolCaster
     */
    private $exprBoolCaster;
    /**
     * @readonly
     * @var \Rector\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(CommentsMerger $commentsMerger, ExprBoolCaster $exprBoolCaster, BetterStandardPrinter $betterStandardPrinter, ValueResolver $valueResolver)
    {
        $this->commentsMerger = $commentsMerger;
        $this->exprBoolCaster = $exprBoolCaster;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Shortens if return false/true to direct return', [new CodeSample(<<<'CODE_SAMPLE'
if (strpos($docToken->getContent(), "\n") === false) {
    return true;
}

return false;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return strpos($docToken->getContent(), "\n") === false;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            $previousStmt = $node->stmts[$key - 1] ?? null;
            if (!$previousStmt instanceof If_) {
                continue;
            }
            $if = $previousStmt;
            if ($this->shouldSkipIfAndReturn($previousStmt, $stmt)) {
                continue;
            }
            $return = $stmt;
            /** @var Return_ $ifInnerNode */
            $ifInnerNode = $if->stmts[0];
            $innerIfInnerNode = $ifInnerNode->expr;
            if (!$innerIfInnerNode instanceof Expr) {
                continue;
            }
            $newReturn = $this->resolveReturn($innerIfInnerNode, $if, $return);
            if (!$newReturn instanceof Return_) {
                continue;
            }
            $this->commentsMerger->keepComments($newReturn, [$if, $return, $ifInnerNode]);
            // remove previous IF
            unset($node->stmts[$key - 1]);
            $node->stmts[$key] = $newReturn;
            return $node;
        }
        return null;
    }
    private function shouldSkipIfAndReturn(If_ $if, Return_ $return) : bool
    {
        if ($if->elseifs !== []) {
            return \true;
        }
        if (!$this->isIfWithSingleReturnExpr($if)) {
            return \true;
        }
        /** @var Return_ $ifInnerNode */
        $ifInnerNode = $if->stmts[0];
        /** @var Expr $returnedExpr */
        $returnedExpr = $ifInnerNode->expr;
        if (!$this->valueResolver->isTrueOrFalse($returnedExpr)) {
            return \true;
        }
        if (!$return->expr instanceof Expr) {
            return \true;
        }
        // negate + negate → skip for now
        if (!$this->valueResolver->isFalse($returnedExpr)) {
            return !$this->valueResolver->isTrueOrFalse($return->expr);
        }
        $condString = $this->betterStandardPrinter->print($if->cond);
        if (\strpos($condString, '!=') === \false) {
            return !$this->valueResolver->isTrueOrFalse($return->expr);
        }
        return !$if->cond instanceof NotIdentical && !$if->cond instanceof NotEqual;
    }
    private function processReturnTrue(If_ $if, Return_ $nextReturn) : Return_
    {
        if ($if->cond instanceof BooleanNot && $nextReturn->expr instanceof Expr && $this->valueResolver->isTrue($nextReturn->expr)) {
            return new Return_($this->exprBoolCaster->boolCastOrNullCompareIfNeeded($if->cond->expr));
        }
        return new Return_($this->exprBoolCaster->boolCastOrNullCompareIfNeeded($if->cond));
    }
    private function processReturnFalse(If_ $if, Return_ $nextReturn) : ?Return_
    {
        if ($if->cond instanceof Identical) {
            $notIdentical = new NotIdentical($if->cond->left, $if->cond->right);
            return new Return_($this->exprBoolCaster->boolCastOrNullCompareIfNeeded($notIdentical));
        }
        if ($if->cond instanceof Equal) {
            $notIdentical = new NotEqual($if->cond->left, $if->cond->right);
            return new Return_($this->exprBoolCaster->boolCastOrNullCompareIfNeeded($notIdentical));
        }
        if (!$nextReturn->expr instanceof Expr) {
            return null;
        }
        if (!$this->valueResolver->isTrue($nextReturn->expr)) {
            return null;
        }
        if ($if->cond instanceof BooleanNot) {
            return new Return_($this->exprBoolCaster->boolCastOrNullCompareIfNeeded($if->cond->expr));
        }
        return new Return_($this->exprBoolCaster->boolCastOrNullCompareIfNeeded(new BooleanNot($if->cond)));
    }
    private function isIfWithSingleReturnExpr(If_ $if) : bool
    {
        if (\count($if->stmts) !== 1) {
            return \false;
        }
        if ($if->else instanceof Else_ || $if->elseifs !== []) {
            return \false;
        }
        $ifInnerNode = $if->stmts[0];
        if (!$ifInnerNode instanceof Return_) {
            return \false;
        }
        // return must have value
        return $ifInnerNode->expr instanceof Expr;
    }
    private function resolveReturn(Expr $innerExpr, If_ $if, Return_ $return) : ?Return_
    {
        if ($this->valueResolver->isTrue($innerExpr)) {
            return $this->processReturnTrue($if, $return);
        }
        if ($this->valueResolver->isFalse($innerExpr)) {
            /** @var Expr $expr */
            $expr = $return->expr;
            if ($if->cond instanceof NotIdentical && $this->valueResolver->isTrue($expr)) {
                $if->cond = new Identical($if->cond->left, $if->cond->right);
                return $this->processReturnTrue($if, $return);
            }
            if ($if->cond instanceof NotEqual && $this->valueResolver->isTrue($expr)) {
                $if->cond = new Equal($if->cond->left, $if->cond->right);
                return $this->processReturnTrue($if, $return);
            }
            return $this->processReturnFalse($if, $return);
        }
        return null;
    }
}
