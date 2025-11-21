<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\Instanceof_;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\SimplerWithIsInstanceOfRector\SimplerWithIsInstanceOfRectorTest
 */
final class SimplerWithIsInstanceOfRector extends AbstractRector
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
        return new RuleDefinition('Replaces use of with, callable and instance assert to simple isInstanceOf() method', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase

final class SomeClass extends TestCase
{
    public function test()
    {
        $someMock = $this->createMock(SomeClass::class)
            ->method('someMethod')
            ->with($this->callable(function ($arg): bool {
                return $arg instanceof SomeType;
            }));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function test()
    {
        $someMock = $this->createMock(SomeClass::class)
            ->method('someMethod')
            ->with($this->isInstanceOf(SomeType::class));
    }
}
CODE_SAMPLE
)]);
    }
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
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node->name, 'with')) {
            return null;
        }
        $withFirstArgValue = $node->getArgs()[0]->value;
        if (!$withFirstArgValue instanceof MethodCall || !$this->isName($withFirstArgValue->name, 'callback')) {
            return null;
        }
        $callableMethodCall = $withFirstArgValue;
        $callableFirstArgValue = $callableMethodCall->getArgs()[0]->value;
        $innerClosure = $callableFirstArgValue;
        if (!$innerClosure instanceof Closure) {
            return null;
        }
        $instanceCheckedClassName = $this->matchSoleInstanceofCheckClassName($innerClosure);
        if (!$instanceCheckedClassName instanceof Node) {
            return null;
        }
        // convert name to expr
        if ($instanceCheckedClassName instanceof Name) {
            $instanceCheckedClassName = $this->nodeFactory->createClassConstFetch($instanceCheckedClassName->toString(), 'class');
        }
        $node->args = [new Arg($this->nodeFactory->createMethodCall('this', 'isInstanceOf', [$instanceCheckedClassName]))];
        return $node;
    }
    /**
     * @return \PhpParser\Node|null|\PhpParser\Node\Expr|\PhpParser\Node\Name
     */
    private function matchSoleInstanceofCheckClassName(Closure $innerClosure)
    {
        // return + instancecheck only
        $innerClosureStmts = $innerClosure->getStmts();
        if (count($innerClosureStmts) === 2) {
            if (!$innerClosureStmts[1] instanceof Return_) {
                return null;
            }
            $firstStmt = $innerClosureStmts[0];
            if (!$firstStmt instanceof Expression) {
                return null;
            }
            $firstStmtExpr = $firstStmt->expr;
            if (!$firstStmtExpr instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($firstStmtExpr->name, 'assertInstanceOf')) {
                return null;
            }
            return $firstStmtExpr->getArgs()[0]->value;
        }
        if (count($innerClosureStmts) === 1) {
            $onlyStmt = $innerClosureStmts[0];
            if (!$onlyStmt instanceof Return_) {
                return null;
            }
            $returnExpr = $onlyStmt->expr;
            if (!$returnExpr instanceof Instanceof_) {
                return null;
            }
            $instanceofExpr = $returnExpr;
            if (!$instanceofExpr->class instanceof Name) {
                return null;
            }
            return $instanceofExpr->class;
        }
        return null;
    }
}
