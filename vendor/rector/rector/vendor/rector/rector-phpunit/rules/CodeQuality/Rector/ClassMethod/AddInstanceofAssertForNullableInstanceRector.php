<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Foreach_;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\PHPStan\Type\UnionType;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\NullableObjectAssignCollector;
use Rector\PHPUnit\CodeQuality\ValueObject\VariableNameToType;
use Rector\PHPUnit\CodeQuality\ValueObject\VariableNameToTypeCollection;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\ClassMethod\AddInstanceofAssertForNullableInstanceRector\AddInstanceofAssertForNullableInstanceRectorTest
 */
final class AddInstanceofAssertForNullableInstanceRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PHPUnit\CodeQuality\NodeAnalyser\NullableObjectAssignCollector
     */
    private $nullableObjectAssignCollector;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, NullableObjectAssignCollector $nullableObjectAssignCollector)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->nullableObjectAssignCollector = $nullableObjectAssignCollector;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add explicit instance assert between nullable object assign and method call on nullable object (spotted by PHPStan)', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $someObject = $this->getSomeObject();

        $value = $someObject->getSomeMethod();
    }

    private function getSomeObject(): ?SomeClass
    {
        if (mt_rand(0, 1)) {
            return new SomeClass();
        }

        return null;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $someObject = $this->getSomeObject();
        $this->assertInstanceOf(SomeClass::class, $someObject);

        $value = $someObject->getSomeMethod();
    }

    private function getSomeObject(): ?SomeClass
    {
        if (mt_rand(0, 1)) {
            return new SomeClass();
        }

        return null;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Foreach_::class];
    }
    /**
     * @param ClassMethod|Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if ($node->stmts === [] || $node->stmts === null || count($node->stmts) < 2) {
            return null;
        }
        $hasChanged = \false;
        $variableNameToTypeCollection = $this->nullableObjectAssignCollector->collect($node);
        $next = 0;
        foreach ($node->stmts as $key => $stmt) {
            // has callable on nullable variable of already collected name?
            $matchedNullableVariableNameToType = $this->matchedNullableVariableNameToType($stmt, $variableNameToTypeCollection);
            if (!$matchedNullableVariableNameToType instanceof VariableNameToType) {
                continue;
            }
            // adding type here + popping the variable name out
            $assertInstanceOfExpression = $this->createAssertInstanceOf($matchedNullableVariableNameToType);
            array_splice($node->stmts, $key + $next, 0, [$assertInstanceOfExpression]);
            // remove variable name from nullable ones
            $hasChanged = \true;
            // from now on, the variable is not nullable, remove to avoid double asserts
            $variableNameToTypeCollection->remove($matchedNullableVariableNameToType);
            ++$next;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function isNullableType(Type $type): bool
    {
        if (!$type instanceof UnionType) {
            return \false;
        }
        if (!TypeCombinator::containsNull($type)) {
            return \false;
        }
        return count($type->getTypes()) === 2;
    }
    private function createAssertInstanceOf(VariableNameToType $variableNameToType): Expression
    {
        $args = [new Arg(new ClassConstFetch(new FullyQualified($variableNameToType->getObjectType()), 'class')), new Arg(new Variable($variableNameToType->getVariableName()))];
        $methodCall = new MethodCall(new Variable('this'), 'assertInstanceOf', $args);
        return new Expression($methodCall);
    }
    private function matchedNullableVariableNameToType(Stmt $stmt, VariableNameToTypeCollection $variableNameToTypeCollection): ?VariableNameToType
    {
        $matchedNullableVariableNameToType = null;
        $this->traverseNodesWithCallable($stmt, function (Node $node) use ($variableNameToTypeCollection, &$matchedNullableVariableNameToType) {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$node->var instanceof Variable) {
                return null;
            }
            $variableType = $this->getType($node->var);
            if (!$this->isNullableType($variableType)) {
                return null;
            }
            $variableName = $this->getName($node->var);
            if ($variableName === null) {
                return null;
            }
            $matchedNullableVariableNameToType = $variableNameToTypeCollection->matchByVariableName($variableName);
            // is the variable we're interested in?
            return null;
        });
        return $matchedNullableVariableNameToType;
    }
}
