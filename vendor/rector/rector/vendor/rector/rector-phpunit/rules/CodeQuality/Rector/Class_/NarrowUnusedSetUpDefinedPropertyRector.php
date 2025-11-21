<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PhpParser\NodeFinder;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\NodeManipulator\PropertyManipulator;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\NarrowUnusedSetUpDefinedPropertyRector\NarrowUnusedSetUpDefinedPropertyRectorTest
 */
final class NarrowUnusedSetUpDefinedPropertyRector extends AbstractRector
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
    /**
     * @readonly
     * @var \Rector\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    /**
     * @readonly
     * @var \PhpParser\NodeFinder
     */
    private $nodeFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ReflectionResolver $reflectionResolver, PropertyManipulator $propertyManipulator)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->propertyManipulator = $propertyManipulator;
        $this->nodeFinder = new NodeFinder();
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turn property used only in setUp() to variable', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
    private $someServiceMock;

    public function setUp(): void
    {
        $this->someServiceMock = $this->createMock(SomeService::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
    public function setUp(): void
    {
        $someServiceMock = $this->createMock(SomeService::class);
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
        $setUpClassMethod = $node->getMethod(MethodName::SET_UP);
        if (!$setUpClassMethod instanceof ClassMethod) {
            return null;
        }
        $hasChanged = \false;
        $isFinalClass = $node->isFinal();
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        foreach ($node->stmts as $key => $classStmt) {
            if (!$classStmt instanceof Property) {
                continue;
            }
            $property = $classStmt;
            if (count($property->props) !== 1) {
                continue;
            }
            $propertyName = $property->props[0]->name->toString();
            if ($this->shouldSkipProperty($isFinalClass, $property, $classReflection, $propertyName)) {
                continue;
            }
            if ($this->isPropertyUsedOutsideSetUpClassMethod($node, $setUpClassMethod, $property)) {
                continue;
            }
            $hasChanged = \true;
            unset($node->stmts[$key]);
            // change property to variable in setUp() method
            $this->traverseNodesWithCallable($setUpClassMethod, function (Node $node) use ($propertyName): ?Variable {
                if (!$node instanceof PropertyFetch) {
                    return null;
                }
                if (!$this->isName($node->var, 'this')) {
                    return null;
                }
                if (!$this->isName($node->name, $propertyName)) {
                    return null;
                }
                return new Variable($propertyName);
            });
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function isPropertyUsedOutsideSetUpClassMethod(Class_ $class, ClassMethod $setUpClassMethod, Property $property): bool
    {
        $isPropertyUsed = \false;
        $propertyName = $property->props[0]->name->toString();
        foreach ($class->getMethods() as $classMethod) {
            // skip setUp() method
            if ($classMethod === $setUpClassMethod) {
                continue;
            }
            // check if property is used anywhere else than setup
            $usedPropertyFetch = $this->nodeFinder->findFirst($classMethod, function (Node $node) use ($propertyName): bool {
                if (!$node instanceof PropertyFetch) {
                    return \false;
                }
                if (!$this->isName($node->var, 'this')) {
                    return \false;
                }
                return $this->isName($node->name, $propertyName);
            });
            if ($usedPropertyFetch instanceof PropertyFetch) {
                $isPropertyUsed = \true;
            }
        }
        return $isPropertyUsed;
    }
    private function shouldSkipProperty(bool $isFinalClass, Property $property, ClassReflection $classReflection, string $propertyName): bool
    {
        // possibly used by child
        if (!$isFinalClass && !$property->isPrivate()) {
            return \true;
        }
        // possibly used for caching or re-use
        if ($property->isStatic()) {
            return \true;
        }
        if ($property->props[0]->default instanceof Expr) {
            return \true;
        }
        return $this->propertyManipulator->isUsedByTrait($classReflection, $propertyName);
    }
}
