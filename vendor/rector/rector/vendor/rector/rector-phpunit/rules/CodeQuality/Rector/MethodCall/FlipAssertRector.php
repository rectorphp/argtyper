<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Scalar;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\FlipAssertRector\FlipAssertRectorTest
 */
final class FlipAssertRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @var string[]
     */
    private const METHOD_NAMES = ['assertSame', 'assertNotSame', 'assertNotEquals', 'assertEquals', 'assertStringContainsString'];
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns accidentally flipped assert order to right one, with expected expr to left', [new CodeSample(<<<'CODE_SAMPLE'
<?php

namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPUnit\Framework\TestCase;
class SomeTest extends TestCase
{
    public function test()
    {
        $result = '...';
        $this->assertSame($result, 'expected');
    }
}
\class_alias('Argtyper202511\SomeTest', 'Argtyper202511\SomeTest', \false);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
<?php

namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPUnit\Framework\TestCase;
class SomeTest extends TestCase
{
    public function test()
    {
        $result = '...';
        $this->assertSame('expected', $result);
    }
}
\class_alias('Argtyper202511\SomeTest', 'Argtyper202511\SomeTest', \false);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, self::METHOD_NAMES)) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        $secondArg = $node->getArgs()[1];
        // correct location
        if ($this->isScalarValue($firstArg->value)) {
            return null;
        }
        if (!$this->isScalarValue($secondArg->value)) {
            return null;
        }
        $oldArgs = $node->getArgs();
        // flip args
        [$oldArgs[0], $oldArgs[1]] = [$oldArgs[1], $oldArgs[0]];
        $node->args = $oldArgs;
        return $node;
    }
    private function isScalarValue(Expr $expr): bool
    {
        if ($expr instanceof Scalar) {
            return \true;
        }
        if ($expr instanceof ConstFetch) {
            return \true;
        }
        return $expr instanceof ClassConstFetch;
    }
}
