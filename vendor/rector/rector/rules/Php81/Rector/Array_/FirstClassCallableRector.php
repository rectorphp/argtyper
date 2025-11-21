<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\Array_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\ClassConst;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PhpParser\Node\VariadicPlaceholder;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php81\Rector\Array_\FirstClassCallableRector\FirstClassCallableRectorTest
 */
final class FirstClassCallableRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher
     */
    private $arrayCallableMethodMatcher;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector
     */
    private $symfonyPhpClosureDetector;
    public function __construct(ArrayCallableMethodMatcher $arrayCallableMethodMatcher, ReflectionProvider $reflectionProvider, ReflectionResolver $reflectionResolver, SymfonyPhpClosureDetector $symfonyPhpClosureDetector)
    {
        $this->arrayCallableMethodMatcher = $arrayCallableMethodMatcher;
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
        $this->symfonyPhpClosureDetector = $symfonyPhpClosureDetector;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        // see RFC https://wiki.php.net/rfc/first_class_callable_syntax
        return new RuleDefinition('Upgrade array callable to first class callable', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $name = [$this, 'name'];
    }

    public function name()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $name = $this->name(...);
    }

    public function name()
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
        return [Property::class, ClassConst::class, Array_::class, Closure::class];
    }
    /**
     * @param Property|ClassConst|Array_|Closure $node
     * @return StaticCall|MethodCall|null|NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN
     */
    public function refactor(Node $node)
    {
        if ($node instanceof Closure) {
            if ($this->symfonyPhpClosureDetector->detect($node)) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            return null;
        }
        if ($node instanceof Property || $node instanceof ClassConst) {
            return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        }
        $scope = ScopeFetcher::fetch($node);
        $arrayCallable = $this->arrayCallableMethodMatcher->match($node, $scope);
        if (!$arrayCallable instanceof ArrayCallable) {
            return null;
        }
        $callerExpr = $arrayCallable->getCallerExpr();
        if (!$callerExpr instanceof Variable && !$callerExpr instanceof PropertyFetch && !$callerExpr instanceof ClassConstFetch) {
            return null;
        }
        $args = [new VariadicPlaceholder()];
        if ($callerExpr instanceof ClassConstFetch) {
            $type = $this->getType($callerExpr->class);
            if ($type instanceof FullyQualifiedObjectType && $this->isNonStaticOtherObject($type, $arrayCallable, $scope)) {
                return null;
            }
            return new StaticCall($callerExpr->class, $arrayCallable->getMethod(), $args);
        }
        $methodName = $arrayCallable->getMethod();
        $methodCall = new MethodCall($callerExpr, $methodName, $args);
        $classReflection = $this->reflectionResolver->resolveClassReflectionSourceObject($methodCall);
        if ($classReflection instanceof ClassReflection && $classReflection->hasNativeMethod($methodName)) {
            $method = $classReflection->getNativeMethod($methodName);
            if (!$method->isPublic()) {
                return null;
            }
        }
        return $methodCall;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_81;
    }
    private function isNonStaticOtherObject(FullyQualifiedObjectType $fullyQualifiedObjectType, ArrayCallable $arrayCallable, Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
        if ($classReflection instanceof ClassReflection && $classReflection->getName() === $fullyQualifiedObjectType->getClassName()) {
            return \false;
        }
        $arrayClassReflection = $this->reflectionProvider->getClass($arrayCallable->getClass());
        // we're unable to find it
        if (!$arrayClassReflection->hasMethod($arrayCallable->getMethod())) {
            return \false;
        }
        $extendedMethodReflection = $arrayClassReflection->getMethod($arrayCallable->getMethod(), $scope);
        if (!$extendedMethodReflection->isStatic()) {
            return \true;
        }
        return !$extendedMethodReflection->isPublic();
    }
}
