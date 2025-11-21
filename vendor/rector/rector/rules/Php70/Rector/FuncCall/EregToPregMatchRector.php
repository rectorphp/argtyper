<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Concat;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Ternary;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Rector\Php70\EregToPcreTransformer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Php70\Rector\FuncCall\EregToPregMatchRector\EregToPregMatchRectorTest
 */
final class EregToPregMatchRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php70\EregToPcreTransformer
     */
    private $eregToPcreTransformer;
    /**
     * @var array<string, string>
     */
    private const OLD_NAMES_TO_NEW_ONES = ['ereg' => 'preg_match', 'eregi' => 'preg_match', 'ereg_replace' => 'preg_replace', 'eregi_replace' => 'preg_replace', 'split' => 'preg_split', 'spliti' => 'preg_split'];
    public function __construct(EregToPcreTransformer $eregToPcreTransformer)
    {
        $this->eregToPcreTransformer = $eregToPcreTransformer;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::NO_EREG_FUNCTION;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes ereg*() to preg*() calls', [new CodeSample('ereg("hi")', 'preg_match("#hi#");')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, Assign::class];
    }
    /**
     * @param FuncCall|Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof FuncCall) {
            return $this->refactorFuncCall($node);
        }
        if (!$this->isEregFuncCallWithThreeArgs($node->expr)) {
            return null;
        }
        /** @var FuncCall $funcCall */
        $funcCall = $node->expr;
        $node->expr = $this->createTernaryWithStrlenOfFirstMatch($funcCall);
        return $node;
    }
    private function shouldSkipFuncCall(FuncCall $funcCall): bool
    {
        $functionName = $this->getName($funcCall);
        if ($functionName === null) {
            return \true;
        }
        if (!isset(self::OLD_NAMES_TO_NEW_ONES[$functionName])) {
            return \true;
        }
        if ($funcCall->isFirstClassCallable()) {
            return \true;
        }
        return !isset($funcCall->getArgs()[0]);
    }
    private function processStringPattern(FuncCall $funcCall, String_ $string, string $functionName): void
    {
        $pattern = $string->value;
        $pattern = $this->eregToPcreTransformer->transform($pattern, $this->isCaseInsensitiveFunction($functionName));
        $firstArg = $funcCall->getArgs()[0];
        Assert::isInstanceOf($firstArg->value, String_::class);
        $firstArg->value->value = $pattern;
    }
    private function processVariablePattern(FuncCall $funcCall, Variable $variable, string $functionName): void
    {
        $pregQuotePatternNode = $this->nodeFactory->createFuncCall('preg_quote', [new Arg($variable), new Arg(new String_('#'))]);
        $startConcat = new Concat(new String_('#'), $pregQuotePatternNode);
        $endDelimiter = $this->isCaseInsensitiveFunction($functionName) ? '#mi' : '#m';
        $concat = new Concat($startConcat, new String_($endDelimiter));
        /** @var Arg $arg */
        $arg = $funcCall->args[0];
        $arg->value = $concat;
    }
    /**
     * Equivalent of:
     * split(' ', 'hey Tom', 0);
     * ↓
     * preg_split('# #', 'hey Tom', 1);
     */
    private function processSplitLimitArgument(FuncCall $funcCall, string $functionName): void
    {
        if (!isset($funcCall->args[2])) {
            return;
        }
        if (!$funcCall->args[2] instanceof Arg) {
            return;
        }
        if (strncmp($functionName, 'split', strlen('split')) !== 0) {
            return;
        }
        // 3rd argument - $limit, 0 → 1
        if (!$funcCall->args[2]->value instanceof Int_) {
            return;
        }
        /** @var Int_ $limitNumberNode */
        $limitNumberNode = $funcCall->args[2]->value;
        if ($limitNumberNode->value !== 0) {
            return;
        }
        $limitNumberNode->value = 1;
    }
    private function createTernaryWithStrlenOfFirstMatch(FuncCall $funcCall): Ternary
    {
        $thirdArg = $funcCall->getArgs()[2];
        $arrayDimFetch = new ArrayDimFetch($thirdArg->value, new Int_(0));
        $strlenFuncCall = $this->nodeFactory->createFuncCall('strlen', [$arrayDimFetch]);
        return new Ternary($funcCall, $strlenFuncCall, $this->nodeFactory->createFalse());
    }
    private function isCaseInsensitiveFunction(string $functionName): bool
    {
        if (strpos($functionName, 'eregi') !== \false) {
            return \true;
        }
        return strpos($functionName, 'spliti') !== \false;
    }
    private function isEregFuncCallWithThreeArgs(Expr $expr): bool
    {
        if (!$expr instanceof FuncCall) {
            return \false;
        }
        $functionName = $this->getName($expr);
        if (!is_string($functionName)) {
            return \false;
        }
        if (!in_array($functionName, ['ereg', 'eregi'], \true)) {
            return \false;
        }
        return isset($expr->getArgs()[2]);
    }
    private function refactorFuncCall(FuncCall $funcCall): ?FuncCall
    {
        if ($this->shouldSkipFuncCall($funcCall)) {
            return null;
        }
        /** @var string $functionName */
        $functionName = $this->getName($funcCall);
        $firstArg = $funcCall->getArgs()[0];
        $patternExpr = $firstArg->value;
        if ($patternExpr instanceof String_) {
            $this->processStringPattern($funcCall, $patternExpr, $functionName);
        } elseif ($patternExpr instanceof Variable) {
            $this->processVariablePattern($funcCall, $patternExpr, $functionName);
        }
        $this->processSplitLimitArgument($funcCall, $functionName);
        $funcCall->name = new Name(self::OLD_NAMES_TO_NEW_ONES[$functionName]);
        return $funcCall;
    }
}
