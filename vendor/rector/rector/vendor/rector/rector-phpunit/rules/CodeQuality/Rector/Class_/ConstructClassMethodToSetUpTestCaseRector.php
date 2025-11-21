<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\NodeAnalyzer\ClassAnalyzer;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\SetUpMethodDecorator;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/sebastianbergmann/phpunit/issues/3975#issuecomment-562584609
 *
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector\ConstructClassMethodToSetUpTestCaseRectorTest
 */
final class ConstructClassMethodToSetUpTestCaseRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\SetUpMethodDecorator
     */
    private $setUpMethodDecorator;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ClassAnalyzer $classAnalyzer, VisibilityManipulator $visibilityManipulator, SetUpMethodDecorator $setUpMethodDecorator, ReflectionResolver $reflectionResolver)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->classAnalyzer = $classAnalyzer;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->setUpMethodDecorator = $setUpMethodDecorator;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change __construct() method in tests of `PHPUnit\Framework\TestCase` to setUp(), to prevent dangerous override', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private $someValue;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        $this->someValue = 1000;
        parent::__construct($name, $data, $dataName);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private $someValue;

    protected function setUp()
    {
        parent::setUp();

        $this->someValue = 1000;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        if ($this->shouldSkip($node, $constructClassMethod)) {
            return null;
        }
        $addedStmts = $this->resolveStmtsToAddToSetUp($constructClassMethod);
        $setUpClassMethod = $node->getMethod(MethodName::SET_UP);
        if (!$setUpClassMethod instanceof ClassMethod) {
            // no setUp() method yet, rename it to setUp :)
            $constructClassMethod->name = new Identifier(MethodName::SET_UP);
            $constructClassMethod->params = [];
            $constructClassMethod->stmts = $addedStmts;
            $this->setUpMethodDecorator->decorate($constructClassMethod);
            $this->visibilityManipulator->makeProtected($constructClassMethod);
        } else {
            $stmtKey = $constructClassMethod->getAttribute(AttributeKey::STMT_KEY);
            unset($node->stmts[$stmtKey]);
            $setUpClassMethod->stmts = array_merge((array) $setUpClassMethod->stmts, $addedStmts);
        }
        return $node;
    }
    private function shouldSkip(Class_ $class, ClassMethod $classMethod): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($class);
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        $currentParent = current($classReflection->getParents());
        if (!$currentParent instanceof ClassReflection) {
            return \true;
        }
        if ($currentParent->getName() !== 'PHPUnit\Framework\TestCase') {
            return \true;
        }
        $paramNames = [];
        foreach ($classMethod->params as $param) {
            $paramNames[] = $this->getName($param);
        }
        $isFoundParamUsed = \false;
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $subNode) use ($paramNames, &$isFoundParamUsed): ?int {
            if ($subNode instanceof StaticCall && $this->isName($subNode->name, MethodName::CONSTRUCT)) {
                return NodeVisitor::DONT_TRAVERSE_CHILDREN;
            }
            if ($subNode instanceof Variable && $this->isNames($subNode, $paramNames)) {
                $isFoundParamUsed = \true;
                return NodeVisitor::STOP_TRAVERSAL;
            }
            return null;
        });
        return $isFoundParamUsed;
    }
    /**
     * @return Stmt[]
     */
    private function resolveStmtsToAddToSetUp(ClassMethod $constructClassMethod): array
    {
        $constructorStmts = (array) $constructClassMethod->stmts;
        // remove parent call
        foreach ($constructorStmts as $key => $constructorStmt) {
            if ($constructorStmt instanceof Expression) {
                $constructorStmt = clone $constructorStmt->expr;
            }
            if (!$this->isParentCallNamed($constructorStmt, MethodName::CONSTRUCT)) {
                continue;
            }
            unset($constructorStmts[$key]);
        }
        return $constructorStmts;
    }
    private function isParentCallNamed(Node $node, string $desiredMethodName): bool
    {
        if (!$node instanceof StaticCall) {
            return \false;
        }
        if ($node->class instanceof Expr) {
            return \false;
        }
        if (!$this->isName($node->class, 'parent')) {
            return \false;
        }
        if ($node->name instanceof Expr) {
            return \false;
        }
        return $this->isName($node->name, $desiredMethodName);
    }
    private function shouldSkipClass(Class_ $class): bool
    {
        $className = $this->getName($class);
        // probably helper class with access to protected methods like createMock()
        if (substr_compare((string) $className, 'Test', -strlen('Test')) !== 0 && substr_compare((string) $className, 'TestCase', -strlen('TestCase')) !== 0) {
            return \true;
        }
        return $this->classAnalyzer->isAnonymousClass($class);
    }
}
