<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\NodeFinder;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Argtyper202511\Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\LocallyCalledStaticMethodToNonStaticRectorTest
 */
final class LocallyCalledStaticMethodToNonStaticRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard
     */
    private $classMethodVisibilityGuard;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher
     */
    private $arrayCallableMethodMatcher;
    public function __construct(ClassMethodVisibilityGuard $classMethodVisibilityGuard, VisibilityManipulator $visibilityManipulator, ReflectionResolver $reflectionResolver, ArrayCallableMethodMatcher $arrayCallableMethodMatcher)
    {
        $this->classMethodVisibilityGuard = $classMethodVisibilityGuard;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->reflectionResolver = $reflectionResolver;
        $this->arrayCallableMethodMatcher = $arrayCallableMethodMatcher;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change static method and local-only calls to non-static', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        self::someStatic();
    }

    private static function someStatic()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->someStatic();
    }

    private function someStatic()
    {
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
    public function refactor(Node $node): ?Class_
    {
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPrivate()) {
                continue;
            }
            $changedClassMethod = $this->refactorClassMethod($node, $classMethod);
            if ($changedClassMethod instanceof ClassMethod) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function refactorClassMethod(Class_ $class, ClassMethod $classMethod): ?ClassMethod
    {
        if (!$classMethod->isStatic()) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if ($this->classMethodVisibilityGuard->isClassMethodVisibilityGuardedByParent($classMethod, $classReflection)) {
            return null;
        }
        if ($this->isClassMethodCalledInAnotherStaticClassMethod($class, $classMethod)) {
            return null;
        }
        if ($this->isNeverCalled($class, $classMethod)) {
            return null;
        }
        // replace all the calls
        $classMethodName = $this->getName($classMethod);
        $className = $this->getName($class) ?? '';
        $shouldSkip = \false;
        $this->traverseNodesWithCallable($class->getMethods(), function (Node $node) use (&$shouldSkip, $classMethodName, $className): ?int {
            if (($node instanceof Closure || $node instanceof ArrowFunction) && $node->static) {
                $this->traverseNodesWithCallable($node->getStmts(), function (Node $subNode) use (&$shouldSkip, $classMethodName, $className): ?int {
                    if (!$subNode instanceof StaticCall) {
                        return null;
                    }
                    if (!$this->isNames($subNode->class, ['self', 'static', $className])) {
                        return null;
                    }
                    if (!$this->isName($subNode->name, $classMethodName)) {
                        return null;
                    }
                    $shouldSkip = \true;
                    return NodeVisitor::STOP_TRAVERSAL;
                });
                if ($shouldSkip) {
                    return NodeVisitor::STOP_TRAVERSAL;
                }
                return null;
            }
            return null;
        });
        if ($shouldSkip) {
            return null;
        }
        $this->traverseNodesWithCallable($class->getMethods(), function (Node $node) use ($classMethodName, $className): ?MethodCall {
            if (!$node instanceof StaticCall) {
                return null;
            }
            if (!$this->isNames($node->class, ['self', 'static', $className])) {
                return null;
            }
            if (!$this->isName($node->name, $classMethodName)) {
                return null;
            }
            return new MethodCall(new Variable('this'), $classMethodName, $node->args);
        });
        // change static calls to non-static ones, but only if in non-static method!!!
        $this->visibilityManipulator->makeNonStatic($classMethod);
        return $classMethod;
    }
    /**
     * If the static class method is called in another static class method,
     * we should keep it to avoid calling $this in static
     */
    private function isClassMethodCalledInAnotherStaticClassMethod(Class_ $class, ClassMethod $classMethod): bool
    {
        $currentClassNamespacedName = (string) $this->getName($class);
        $currentClassMethodName = $this->getName($classMethod);
        $isInsideStaticClassMethod = \false;
        // check if called static call somewhere in class, but only in static methods
        foreach ($class->getMethods() as $checkedClassMethod) {
            // not a problem
            if (!$checkedClassMethod->isStatic()) {
                continue;
            }
            $this->traverseNodesWithCallable($checkedClassMethod, function (Node $node) use ($currentClassNamespacedName, $currentClassMethodName, &$isInsideStaticClassMethod): ?int {
                if ($node instanceof Array_) {
                    $scope = $node->getAttribute(AttributeKey::SCOPE);
                    if ($scope instanceof Scope && $this->arrayCallableMethodMatcher->match($node, $scope, $currentClassMethodName)) {
                        $isInsideStaticClassMethod = \true;
                        return NodeVisitor::STOP_TRAVERSAL;
                    }
                }
                if (!$node instanceof StaticCall) {
                    return null;
                }
                if (!$this->isNames($node->class, ['self', 'static', $currentClassNamespacedName])) {
                    return null;
                }
                if (!$this->isName($node->name, $currentClassMethodName)) {
                    return null;
                }
                $isInsideStaticClassMethod = \true;
                return NodeVisitor::STOP_TRAVERSAL;
            });
            if ($isInsideStaticClassMethod) {
                return $isInsideStaticClassMethod;
            }
        }
        return \false;
    }
    /**
     * In case of never called method call,
     * it should be skipped and handled by another dead-code rule
     */
    private function isNeverCalled(Class_ $class, ClassMethod $classMethod): bool
    {
        $currentMethodName = $this->getName($classMethod);
        $nodeFinder = new NodeFinder();
        $methodCall = $nodeFinder->findFirst($class, function (Node $node) use ($currentMethodName): bool {
            if ($node instanceof MethodCall && $node->var instanceof Variable && $this->isName($node->var, 'this') && $this->isName($node->name, $currentMethodName)) {
                return \true;
            }
            return $node instanceof StaticCall && $this->isNames($node->class, ['self', 'static']) && $this->isName($node->name, $currentMethodName);
        });
        return !$methodCall instanceof Node;
    }
}
