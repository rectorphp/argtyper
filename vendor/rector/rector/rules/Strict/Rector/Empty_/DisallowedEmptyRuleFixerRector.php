<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Strict\Rector\Empty_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanOr;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\Empty_;
use Argtyper202511\PhpParser\Node\Expr\Isset_;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\NodeAnalyzer\ExprAnalyzer;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Strict\NodeAnalyzer\UninitializedPropertyAnalyzer;
use Argtyper202511\Rector\Strict\NodeFactory\ExactCompareFactory;
use Argtyper202511\Rector\Strict\Rector\AbstractFalsyScalarRuleFixerRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector\DisallowedEmptyRuleFixerRectorTest
 */
final class DisallowedEmptyRuleFixerRector extends AbstractFalsyScalarRuleFixerRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\Strict\NodeFactory\ExactCompareFactory
     */
    private $exactCompareFactory;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    /**
     * @readonly
     * @var \Rector\Strict\NodeAnalyzer\UninitializedPropertyAnalyzer
     */
    private $uninitializedPropertyAnalyzer;
    public function __construct(ExactCompareFactory $exactCompareFactory, ExprAnalyzer $exprAnalyzer, UninitializedPropertyAnalyzer $uninitializedPropertyAnalyzer)
    {
        $this->exactCompareFactory = $exactCompareFactory;
        $this->exprAnalyzer = $exprAnalyzer;
        $this->uninitializedPropertyAnalyzer = $uninitializedPropertyAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change empty() check to explicit strict comparisons', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeEmptyArray
{
    public function run(array $items)
    {
        return empty($items);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeEmptyArray
{
    public function run(array $items)
    {
        return $items === [];
    }
}
CODE_SAMPLE
, [self::TREAT_AS_NON_EMPTY => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Empty_::class, BooleanNot::class];
    }
    /**
     * @param Empty_|BooleanNot $node
     */
    public function refactor(Node $node): ?\Argtyper202511\PhpParser\Node\Expr
    {
        $scope = ScopeFetcher::fetch($node);
        if ($node instanceof BooleanNot) {
            return $this->refactorBooleanNot($node, $scope);
        }
        if ($node->expr instanceof ArrayDimFetch) {
            return null;
        }
        return $this->refactorEmpty($node, $scope, $this->treatAsNonEmpty);
    }
    private function refactorBooleanNot(BooleanNot $booleanNot, Scope $scope): ?\Argtyper202511\PhpParser\Node\Expr
    {
        if (!$booleanNot->expr instanceof Empty_) {
            return null;
        }
        $empty = $booleanNot->expr;
        if ($empty->expr instanceof ArrayDimFetch) {
            return $this->createDimFetchBooleanAnd($empty->expr);
        }
        if ($this->exprAnalyzer->isNonTypedFromParam($empty->expr)) {
            return null;
        }
        $emptyExprType = $scope->getNativeType($empty->expr);
        $result = $this->exactCompareFactory->createNotIdenticalFalsyCompare($emptyExprType, $empty->expr, $this->treatAsNonEmpty);
        if (!$result instanceof Expr) {
            return null;
        }
        if ($this->uninitializedPropertyAnalyzer->isUninitialized($empty->expr)) {
            return new BooleanAnd(new Isset_([$empty->expr]), $result);
        }
        return $result;
    }
    private function refactorEmpty(Empty_ $empty, Scope $scope, bool $treatAsNonEmpty): ?\Argtyper202511\PhpParser\Node\Expr
    {
        if ($this->exprAnalyzer->isNonTypedFromParam($empty->expr)) {
            return null;
        }
        $exprType = $scope->getNativeType($empty->expr);
        $result = $this->exactCompareFactory->createIdenticalFalsyCompare($exprType, $empty->expr, $treatAsNonEmpty);
        if (!$result instanceof Expr) {
            return null;
        }
        if ($this->uninitializedPropertyAnalyzer->isUninitialized($empty->expr)) {
            return new BooleanOr(new BooleanNot(new Isset_([$empty->expr])), $result);
        }
        return $result;
    }
    private function createDimFetchBooleanAnd(ArrayDimFetch $arrayDimFetch): ?BooleanAnd
    {
        $exprType = $this->nodeTypeResolver->getNativeType($arrayDimFetch);
        $isset = new Isset_([$arrayDimFetch]);
        $compareExpr = $this->exactCompareFactory->createNotIdenticalFalsyCompare($exprType, $arrayDimFetch, \false);
        if (!$compareExpr instanceof Expr) {
            return null;
        }
        return new BooleanAnd($isset, $compareExpr);
    }
}
