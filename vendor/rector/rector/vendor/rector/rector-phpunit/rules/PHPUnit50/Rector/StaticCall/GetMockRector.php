<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\PHPUnit50\Rector\StaticCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/sebastianbergmann/phpunit/blob/5.7.0/src/Framework/TestCase.php#L1623
 * @changelog https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L1452
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit50\Rector\StaticCall\GetMockRector\GetMockRectorTest
 */
final class GetMockRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ReflectionResolver $reflectionResolver)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns getMock*() methods to createMock()', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $classMock = $this->getMock("Class");
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $classMock = $this->createMock("Class");
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
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['getMock', 'getMockWithoutInvokingTheOriginalConstructor'])) {
            return null;
        }
        if ($node instanceof MethodCall && $node->var instanceof MethodCall) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflectionSourceObject($node);
        if ($classReflection instanceof ClassReflection && $classReflection->getName() !== 'PHPUnit\\Framework\\TestCase') {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        // narrow args to one
        if (\count($node->args) > 1) {
            $node->args = [$node->getArgs()[0]];
        }
        $node->name = new Identifier('createMock');
        return $node;
    }
}
