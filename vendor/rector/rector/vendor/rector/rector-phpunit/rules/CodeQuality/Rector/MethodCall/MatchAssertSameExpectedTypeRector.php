<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\MatchAssertSameExpectedTypeRector\MatchAssertSameExpectedTypeRectorTest
 */
final class MatchAssertSameExpectedTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Correct expected type in assertSame() method to match strict type of passed variable', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    public function test()
    {
        $this->assertSame('123', $this->getOrderId());
    }

    private function getOrderId(): int
    {
        return 123;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    public function test()
    {
        $this->assertSame(123, $this->getOrderId());
    }

    private function getOrderId(): int
    {
        return 123;
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
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertSame', 'assertEquals'])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (\count($node->getArgs()) < 2) {
            return null;
        }
        $expectedArg = $node->getArgs()[0];
        if (!$expectedArg->value instanceof String_ && !$expectedArg->value instanceof Int_) {
            return null;
        }
        $expectedType = $this->getType($expectedArg->value);
        $variableExpr = $node->getArgs()[1]->value;
        $variableType = $this->nodeTypeResolver->getNativeType($variableExpr);
        $directVariableType = TypeCombinator::removeNull($variableType);
        if ($expectedType->isLiteralString()->yes() && $directVariableType->isInteger()->yes()) {
            // update expected type to provided type
            $expectedArg->value = new Int_((int) $expectedArg->value->value);
            return $node;
        }
        if ($expectedType->isInteger()->yes() && $directVariableType->isString()->yes()) {
            if ($this->isName($node->name, 'assertEquals')) {
                return null;
            }
            // update expected type to provided type
            $expectedArg->value = new String_((string) $expectedArg->value->value);
            return $node;
        }
        return null;
    }
}
