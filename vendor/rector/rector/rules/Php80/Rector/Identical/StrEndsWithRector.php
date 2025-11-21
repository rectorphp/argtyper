<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\Rector\Identical;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Equal;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotEqual;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\UnaryMinus;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\NodeAnalyzer\BinaryOpAnalyzer;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\FuncCallAndExpr;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\ValueObject\PolyfillPackage;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Rector\VersionBonding\Contract\RelatedPolyfillInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\StrEndsWithRectorTest
 */
final class StrEndsWithRector extends AbstractRector implements MinPhpVersionInterface, RelatedPolyfillInterface
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\BinaryOpAnalyzer
     */
    private $binaryOpAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(BinaryOpAnalyzer $binaryOpAnalyzer, ValueResolver $valueResolver)
    {
        $this->binaryOpAnalyzer = $binaryOpAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::STR_ENDS_WITH;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change helper functions to `str_ends_with()`', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $isMatch = substr($haystack, -strlen($needle)) === $needle;

        $isNotMatch = substr($haystack, -strlen($needle)) !== $needle;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $isMatch = str_ends_with($haystack, $needle);

        $isNotMatch = !str_ends_with($haystack, $needle);
    }
}
CODE_SAMPLE
), new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $isMatch = substr($haystack, -9) === 'hardcoded;

        $isNotMatch = substr($haystack, -9) !== 'hardcoded';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $isMatch = str_ends_with($haystack, 'hardcoded');

        $isNotMatch = !str_ends_with($haystack, 'hardcoded');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Identical::class, NotIdentical::class, Equal::class, NotEqual::class];
    }
    /**
     * @param Identical|NotIdentical|Equal|NotEqual $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->refactorSubstr($node) ?? $this->refactorSubstrCompare($node);
    }
    public function providePolyfillPackage(): string
    {
        return PolyfillPackage::PHP_80;
    }
    /**
     * Covers:
     * $isMatch = substr($haystack, -strlen($needle)) === $needle;
     * $isMatch = 'needle' === substr($haystack, -6)
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\BooleanNot|null
     */
    private function refactorSubstr(BinaryOp $binaryOp)
    {
        if ($binaryOp->left instanceof FuncCall && $this->isName($binaryOp->left, 'substr')) {
            $substrFuncCall = $binaryOp->left;
            $comparedNeedleExpr = $binaryOp->right;
        } elseif ($binaryOp->right instanceof FuncCall && $this->isName($binaryOp->right, 'substr')) {
            $substrFuncCall = $binaryOp->right;
            $comparedNeedleExpr = $binaryOp->left;
        } else {
            return null;
        }
        if ($substrFuncCall->isFirstClassCallable()) {
            return null;
        }
        if (count($substrFuncCall->getArgs()) < 2) {
            return null;
        }
        $needle = $substrFuncCall->getArgs()[1]->value;
        if (!$this->isUnaryMinusStrlenFuncCallArgValue($needle, $comparedNeedleExpr) && !$this->isHardCodedLNumberAndString($needle, $comparedNeedleExpr)) {
            return null;
        }
        $haystack = $substrFuncCall->getArgs()[0]->value;
        $isPositive = $binaryOp instanceof Identical || $binaryOp instanceof Equal;
        return $this->buildReturnNode($haystack, $comparedNeedleExpr, $isPositive);
    }
    /**
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\BooleanNot|null
     */
    private function refactorSubstrCompare(BinaryOp $binaryOp)
    {
        $funcCallAndExpr = $this->binaryOpAnalyzer->matchFuncCallAndOtherExpr($binaryOp, 'substr_compare');
        if (!$funcCallAndExpr instanceof FuncCallAndExpr) {
            return null;
        }
        $expr = $funcCallAndExpr->getExpr();
        if (!$this->valueResolver->isValue($expr, 0)) {
            return null;
        }
        $substrCompareFuncCall = $funcCallAndExpr->getFuncCall();
        $args = $substrCompareFuncCall->getArgs();
        if (count($args) < 2) {
            return null;
        }
        $haystack = $args[0]->value;
        $needle = $args[1]->value;
        $thirdArgValue = $args[2]->value;
        $isCaseInsensitiveValue = isset($args[4]) ? $this->valueResolver->getValue($args[4]->value) : null;
        // is case insensitive → not valid replacement
        if ($isCaseInsensitiveValue === \true) {
            return null;
        }
        if (!$this->isUnaryMinusStrlenFuncCallArgValue($thirdArgValue, $needle) && !$this->isHardCodedLNumberAndString($thirdArgValue, $needle)) {
            return null;
        }
        $isPositive = $binaryOp instanceof Identical || $binaryOp instanceof Equal;
        return $this->buildReturnNode($haystack, $needle, $isPositive);
    }
    private function isUnaryMinusStrlenFuncCallArgValue(Expr $substrOffset, Expr $needle): bool
    {
        if (!$substrOffset instanceof UnaryMinus) {
            return \false;
        }
        if (!$substrOffset->expr instanceof FuncCall) {
            return \false;
        }
        $funcCall = $substrOffset->expr;
        if (!$this->isName($funcCall, 'strlen')) {
            return \false;
        }
        if (!isset($funcCall->getArgs()[0])) {
            return \false;
        }
        if (!$funcCall->args[0] instanceof Arg) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($funcCall->args[0]->value, $needle);
    }
    private function isHardCodedLNumberAndString(Expr $substrOffset, Expr $needle): bool
    {
        if (!$substrOffset instanceof UnaryMinus) {
            return \false;
        }
        if (!$substrOffset->expr instanceof Int_) {
            return \false;
        }
        $lNumber = $substrOffset->expr;
        if (!$needle instanceof String_) {
            return \false;
        }
        return $lNumber->value === strlen($needle->value);
    }
    /**
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\BooleanNot
     */
    private function buildReturnNode(Expr $haystack, Expr $needle, bool $isPositive)
    {
        $funcCall = $this->nodeFactory->createFuncCall('str_ends_with', [$haystack, $needle]);
        if (!$isPositive) {
            return new BooleanNot($funcCall);
        }
        return $funcCall;
    }
}
