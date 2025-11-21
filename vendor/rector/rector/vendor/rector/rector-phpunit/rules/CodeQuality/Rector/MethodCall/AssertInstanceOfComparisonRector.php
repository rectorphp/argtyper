<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Instanceof_;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\AssertInstanceOfComparisonRector\AssertInstanceOfComparisonRectorTest
 */
final class AssertInstanceOfComparisonRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator
     */
    private $identifierManipulator;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @var array<string, string>
     */
    private const RENAME_METHODS_MAP = ['assertTrue' => 'assertInstanceOf', 'assertFalse' => 'assertNotInstanceOf'];
    public function __construct(IdentifierManipulator $identifierManipulator, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->identifierManipulator = $identifierManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase', [new CodeSample('$this->assertTrue($foo instanceof Foo, "message");', '$this->assertInstanceOf("Foo", $foo, "message");'), new CodeSample('$this->assertFalse($foo instanceof Foo, "message");', '$this->assertNotInstanceOf("Foo", $foo, "message");'), new CodeSample('$this->assertNotEquals(SomeInstance::class, get_class($value));', '$this->assertNotInstanceOf(SomeInstance::class, $value);')]);
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
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if ($this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertSame', 'assertNotSame', 'assertEquals', 'assertNotEquals'])) {
            return $this->refactorGetClass($node);
        }
        $oldMethodNames = array_keys(self::RENAME_METHODS_MAP);
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, $oldMethodNames)) {
            return null;
        }
        $firstArgumentValue = $node->getArgs()[0]->value;
        if (!$firstArgumentValue instanceof Instanceof_) {
            return null;
        }
        $this->identifierManipulator->renameNodeWithMap($node, self::RENAME_METHODS_MAP);
        $this->changeArgumentsOrder($node);
        return $node;
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    private function refactorGetClass(Node $node): ?Node
    {
        // we need 2 args
        if (!isset($node->args[1])) {
            return null;
        }
        $secondArgument = $node->getArgs()[1];
        $secondArgumentValue = $secondArgument->value;
        if ($secondArgumentValue instanceof FuncCall && $this->isName($secondArgumentValue->name, 'get_class')) {
            $node->args[1] = $secondArgumentValue->getArgs()[0];
            if ($this->isNames($node->name, ['assertSame', 'assertEquals'])) {
                $node->name = new Identifier('assertInstanceOf');
            } elseif ($this->isNames($node->name, ['assertNotSame', 'assertNotEquals'])) {
                $node->name = new Identifier('assertNotInstanceOf');
            }
            return $node;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function changeArgumentsOrder($node): void
    {
        $oldArguments = $node->getArgs();
        /** @var Instanceof_ $comparison */
        $comparison = $oldArguments[0]->value;
        $argument = $comparison->expr;
        unset($oldArguments[0]);
        if ($comparison->class instanceof Variable) {
            $firstArgument = new Arg($comparison->class);
        } else {
            $className = $this->getName($comparison->class);
            if ($className === null) {
                throw new ShouldNotHappenException();
            }
            $firstArgument = new Arg($this->nodeFactory->createClassConstReference($className));
        }
        $node->args = array_merge([$firstArgument, new Arg($argument)], $oldArguments);
    }
}
