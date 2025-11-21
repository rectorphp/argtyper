<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\NodeAnalyzer\ArgsAnalyzer;
use Argtyper202511\Rector\NodeAnalyzer\ExprAnalyzer;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveArgumentFromDefaultParentCallRector\RemoveArgumentFromDefaultParentCallRectorTest
 */
final class RemoveArgumentFromDefaultParentCallRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(ReflectionResolver $reflectionResolver, ArgsAnalyzer $argsAnalyzer, ValueResolver $valueResolver, ExprAnalyzer $exprAnalyzer)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->argsAnalyzer = $argsAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove default argument from parent call', [new CodeSample(<<<'CODE_SAMPLE'
class SomeParent
{
    public function __construct(array $param = [])
    {
    }
}

class ChildClass extends SomeParent
{
    public function __construct(string $differentParam)
    {
        init($differentParam);

        parent::__construct([]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeParent
{
    public function __construct(array $param = [])
    {
    }
}

class ChildClass extends SomeParent
{
    public function __construct(string $differentParam)
    {
        init($differentParam);

        parent::__construct();
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
    public function refactor(Node $node) : ?Class_
    {
        if (!$node->extends instanceof FullyQualified) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $ancestors = \array_filter($classReflection->getAncestors(), function (ClassReflection $ancestorClassReflection) use($classReflection) : bool {
            return $classReflection->isClass() && $ancestorClassReflection->getName() !== $classReflection->getName();
        });
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->isPrivate()) {
                continue;
            }
            if ($classMethod->isAbstract()) {
                continue;
            }
            foreach ((array) $classMethod->stmts as $stmt) {
                if (!$stmt instanceof Expression) {
                    continue;
                }
                if (!$stmt->expr instanceof StaticCall) {
                    continue;
                }
                if (!$this->isName($stmt->expr->class, 'parent')) {
                    continue;
                }
                if ($stmt->expr->isFirstClassCallable()) {
                    continue;
                }
                $args = $stmt->expr->getArgs();
                if ($args === []) {
                    continue;
                }
                if ($this->argsAnalyzer->hasNamedArg($args)) {
                    continue;
                }
                $methodName = $this->getName($stmt->expr->name);
                if ($methodName === null) {
                    continue;
                }
                foreach ($ancestors as $ancestor) {
                    $nativeClassReflection = $ancestor->getNativeReflection();
                    if (!$nativeClassReflection->hasMethod($methodName)) {
                        continue;
                    }
                    $method = $nativeClassReflection->getMethod($methodName);
                    $parameters = $method->getParameters();
                    $totalParameters = \count($parameters);
                    $justChanged = \false;
                    for ($index = $totalParameters - 1; $index >= 0; --$index) {
                        if (!$parameters[$index]->isDefaultValueAvailable()) {
                            break;
                        }
                        // already passed
                        if (!isset($args[$index])) {
                            break;
                        }
                        // only literal values
                        if ($this->exprAnalyzer->isDynamicExpr($args[$index]->value)) {
                            break;
                        }
                        // on decrement loop, when next arg is not removed, then current can't be removed
                        if (isset($args[$index + 1])) {
                            break;
                        }
                        $defaultValue = $parameters[$index]->getDefaultValue();
                        if ($defaultValue === $this->valueResolver->getValue($args[$index]->value)) {
                            unset($args[$index]);
                            $hasChanged = \true;
                            $justChanged = \true;
                        }
                    }
                    if ($justChanged) {
                        $stmt->expr->args = \array_values($args);
                    }
                    break;
                }
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
