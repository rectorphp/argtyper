<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php74\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\NodeAnalyzer\ArgsAnalyzer;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php74\Rector\FuncCall\MoneyFormatToNumberFormatRector\MoneyFormatToNumberFormatRectorTest
 */
final class MoneyFormatToNumberFormatRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ArgsAnalyzer $argsAnalyzer, ValueResolver $valueResolver)
    {
        $this->argsAnalyzer = $argsAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_MONEY_FORMAT;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change `money_format()` to equivalent `number_format()`', [new CodeSample(<<<'CODE_SAMPLE'
$value = money_format('%i', $value);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = number_format(round($value, 2, PHP_ROUND_HALF_ODD), 2, '.', '');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?FuncCall
    {
        if (!$this->isName($node, 'money_format')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        if ($this->argsAnalyzer->hasNamedArg($args)) {
            return null;
        }
        $formatValue = $args[0]->value;
        if (!$this->valueResolver->isValue($formatValue, '%i')) {
            return null;
        }
        return $this->warpInNumberFormatFuncCall($node, $args[1]->value);
    }
    private function warpInNumberFormatFuncCall(FuncCall $funcCall, Expr $expr) : FuncCall
    {
        $roundFuncCall = $this->nodeFactory->createFuncCall('round', [$expr, new Int_(2), new ConstFetch(new Name('PHP_ROUND_HALF_ODD'))]);
        $funcCall->name = new Name('number_format');
        $funcCall->args = [new Arg($roundFuncCall), new Arg(new Int_(2)), new Arg(new String_('.')), new Arg(new String_(''))];
        return $funcCall;
    }
}
