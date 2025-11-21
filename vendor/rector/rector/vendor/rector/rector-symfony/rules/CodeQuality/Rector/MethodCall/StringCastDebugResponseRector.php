<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\CodeQuality\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Cast\String_;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\MethodCall\StringCastDebugResponseRector\StringCastDebugResponseRectorTest
 */
final class StringCastDebugResponseRector extends AbstractRector
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
        return new RuleDefinition('Cast responses in PHPUnit assert message to string, as required by PHPUnit ', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function run()
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $this->processResult();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function run()
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $this->processResult();

        $this->assertSame(200, $response->getStatusCode(), (string) $response->getContent());
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
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if (!$this->isName($node->name, 'assertSame')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        // there cannot be any custom message
        $args = $node->getArgs();
        if (\count($args) !== 3) {
            return null;
        }
        $thirdArg = $args[2];
        if (!$thirdArg->value instanceof MethodCall) {
            return null;
        }
        if (!$this->isName($thirdArg->value->name, 'getContent')) {
            return null;
        }
        $thirdArg->value = new String_($thirdArg->value);
        return $node;
    }
}
