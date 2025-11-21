<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Coalesce;
use Argtyper202511\PhpParser\Node\NullableType;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
use Rector\NodeManipulator\StmtsManipulator;
use Rector\Php81\NodeAnalyzer\CoalescePropertyAssignMatcher;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\NewInInitializerRectorTest
 */
final class NewInInitializerRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer
     */
    private $classChildAnalyzer;
    /**
     * @readonly
     * @var \Rector\Php81\NodeAnalyzer\CoalescePropertyAssignMatcher
     */
    private $coalescePropertyAssignMatcher;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\StmtsManipulator
     */
    private $stmtsManipulator;
    public function __construct(ReflectionResolver $reflectionResolver, ClassChildAnalyzer $classChildAnalyzer, CoalescePropertyAssignMatcher $coalescePropertyAssignMatcher, StmtsManipulator $stmtsManipulator)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->classChildAnalyzer = $classChildAnalyzer;
        $this->coalescePropertyAssignMatcher = $coalescePropertyAssignMatcher;
        $this->stmtsManipulator = $stmtsManipulator;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace property declaration of new state with direct new', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private Logger $logger;

    public function __construct(
        ?Logger $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private ?Logger $logger = new NullLogger,
    ) {
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
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        $params = $this->resolveParams($constructClassMethod);
        if ($params === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ((array) $constructClassMethod->stmts as $key => $stmt) {
            foreach ($params as $param) {
                $paramName = $this->getName($param);
                if ($param->type instanceof NullableType && $param->default === null) {
                    continue;
                }
                $coalesce = $this->coalescePropertyAssignMatcher->matchCoalesceAssignsToLocalPropertyNamed($stmt, $paramName);
                if (!$coalesce instanceof Coalesce) {
                    continue;
                }
                if ($this->stmtsManipulator->isVariableUsedInNextStmt($constructClassMethod, $key + 1, $paramName)) {
                    continue;
                }
                $param->default = $coalesce->right;
                unset($constructClassMethod->stmts[$key]);
                $this->processPropertyPromotion($node, $param, $paramName);
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::NEW_INITIALIZERS;
    }
    /**
     * @return Param[]
     */
    private function resolveParams(ClassMethod $classMethod): array
    {
        $params = $this->matchConstructorParams($classMethod);
        if ($params === []) {
            return [];
        }
        if ($this->isOverrideAbstractMethod($classMethod)) {
            return [];
        }
        return $params;
    }
    private function isOverrideAbstractMethod(ClassMethod $classMethod): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        $methodName = $this->getName($classMethod);
        return $classReflection instanceof ClassReflection && $this->classChildAnalyzer->hasAbstractParentClassMethod($classReflection, $methodName);
    }
    private function processPropertyPromotion(Class_ $class, Param $param, string $paramName): void
    {
        foreach ($class->stmts as $key => $stmt) {
            if (!$stmt instanceof Property) {
                continue;
            }
            $property = $stmt;
            if (!$this->isName($stmt, $paramName)) {
                continue;
            }
            $param->flags = $property->flags;
            $param->attrGroups = array_merge($property->attrGroups, $param->attrGroups);
            unset($class->stmts[$key]);
        }
    }
    /**
     * @return Param[]
     */
    private function matchConstructorParams(ClassMethod $classMethod): array
    {
        // skip empty constructor assigns, as we need those here
        if ($classMethod->stmts === null || $classMethod->stmts === []) {
            return [];
        }
        $params = array_filter($classMethod->params, static function (Param $param): bool {
            return $param->type instanceof NullableType;
        });
        if ($params === []) {
            return $params;
        }
        $totalParams = count($classMethod->params);
        foreach (array_keys($params) as $key) {
            for ($iteration = $key + 1; $iteration < $totalParams; ++$iteration) {
                if (isset($classMethod->params[$iteration]) && !$classMethod->params[$iteration]->default instanceof Expr) {
                    return [];
                }
            }
        }
        return $params;
    }
    private function shouldSkipClass(Class_ $class): bool
    {
        if ($class->stmts === []) {
            return \true;
        }
        if ($class->isAbstract()) {
            return \true;
        }
        return $class->isAnonymous();
    }
}
