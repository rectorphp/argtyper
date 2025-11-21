<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\AssertCompareOnCountableWithMethodToAssertCountRector\AssertCompareOnCountableWithMethodToAssertCountRectorTest
 */
final class AssertCompareOnCountableWithMethodToAssertCountRector extends AbstractRector
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
        return new RuleDefinition('Replaces use of assertSame and assertEquals on Countable objects with count method', [new CodeSample(<<<'CODE_SAMPLE'
$this->assertSame(1, $countable->count());
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$this->assertCount(1, $countable);
CODE_SAMPLE
), new CodeSample('$this->assertSame(10, count($anything), "message");', '$this->assertCount(10, $anything, "message");')]);
    }
    /**
     * @return array<class-string<MethodCall|StaticCall>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    public function refactor(Node $node)
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertSame', 'assertNotSame', 'assertEquals', 'assertNotEquals'])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $assertArgs = $node->getArgs();
        if (count($assertArgs) < 2) {
            return null;
        }
        $comparedExpr = $assertArgs[1]->value;
        if ($comparedExpr instanceof FuncCall && $this->isNames($comparedExpr->name, ['count', 'sizeof', 'iterator_count'])) {
            $countArg = $comparedExpr->getArgs()[0];
            $assertArgs[1] = new Arg($countArg->value);
            $node->args = $assertArgs;
            $this->renameMethod($node);
            return $node;
        }
        if ($comparedExpr instanceof MethodCall && $this->isName($comparedExpr->name, 'count') && $comparedExpr->getArgs() === []) {
            $type = $this->getType($comparedExpr->var);
            if ((new ObjectType('Countable'))->isSuperTypeOf($type)->yes()) {
                $args = $assertArgs;
                $args[1] = new Arg($comparedExpr->var);
                $node->args = $args;
                $this->renameMethod($node);
            }
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function renameMethod($node): void
    {
        if ($this->isNames($node->name, ['assertSame', 'assertEquals'])) {
            $node->name = new Identifier('assertCount');
        } elseif ($this->isNames($node->name, ['assertNotSame', 'assertNotEquals'])) {
            $node->name = new Identifier('assertNotCount');
        }
    }
}
