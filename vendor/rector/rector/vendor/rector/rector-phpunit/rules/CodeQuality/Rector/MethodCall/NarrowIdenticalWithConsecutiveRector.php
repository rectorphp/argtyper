<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\PrettyPrinter\Standard;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\NarrowIdenticalWithConsecutiveRector\NarrowIdenticalWithConsecutiveRectorTest
 */
final class NarrowIdenticalWithConsecutiveRector extends AbstractRector
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
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Narrow identical withConsecutive() and willReturnOnConsecutiveCalls() to single call', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function run()
    {
        $this->personServiceMock->expects($this->exactly(3))
            ->method('prepare')
            ->withConsecutive(
                [1],
                [1],
                [1],
            )
            ->willReturnOnConsecutiveCalls(
                [2],
                [2],
                [2],
            );
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function run()
    {
        $this->personServiceMock->expects($this->exactly(3))
            ->method('prepare')
            ->with([1])
            ->willReturn([2]);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<MethodCall>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?\Argtyper202511\PhpParser\Node\Expr\MethodCall
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if (!$this->isNames($node->name, ['withConsecutive', 'willReturnOnConsecutiveCalls'])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        // skip as most likely nested array of unique values
        if ($firstArg->unpack) {
            return null;
        }
        $uniqueArgValues = $this->resolveUniqueArgValues($node);
        // multiple unique values
        if (count($uniqueArgValues) !== 1) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        if ($this->isName($node->name, 'withConsecutive')) {
            $node->name = new Identifier('with');
        } else {
            $node->name = new Identifier('willReturn');
        }
        // use simpler with() instead
        $node->args = [new Arg($firstArg->value)];
        return $node;
    }
    /**
     * @return string[]
     */
    private function resolveUniqueArgValues(MethodCall $methodCall): array
    {
        $printerStandard = new Standard();
        $printedValues = [];
        foreach ($methodCall->getArgs() as $arg) {
            $printedValues[] = $printerStandard->prettyPrintExpr($arg->value);
        }
        return array_unique($printedValues);
    }
}
