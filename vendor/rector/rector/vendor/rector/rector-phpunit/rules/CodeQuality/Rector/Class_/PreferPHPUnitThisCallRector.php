<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\Rector\PHPUnit\CodeQuality\NodeAnalyser\AssertMethodAnalyzer;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector\PreferPHPUnitThisCallRectorTest
 */
final class PreferPHPUnitThisCallRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PHPUnit\CodeQuality\NodeAnalyser\AssertMethodAnalyzer
     */
    private $assertMethodAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, AssertMethodAnalyzer $assertMethodAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->assertMethodAnalyzer = $assertMethodAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes PHPUnit calls from self::assert*() to $this->assert*()', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function run()
    {
        self::assertEquals('expected', $result);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function run()
    {
        $this->assertEquals('expected', $result);
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node, function (Node $node) use(&$hasChanged) {
            $isInsideStaticFunctionLike = $node instanceof ClassMethod && $node->isStatic() || ($node instanceof Closure || $node instanceof ArrowFunction) && $node->static;
            if ($isInsideStaticFunctionLike) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof StaticCall) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            if (!$this->assertMethodAnalyzer->detectTestCaseCall($node)) {
                return null;
            }
            $methodName = $this->getName($node->name);
            if ($methodName === null) {
                return null;
            }
            $hasChanged = \true;
            return $this->nodeFactory->createMethodCall('this', $methodName, $node->getArgs());
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
