<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\Rector\Foreach_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Foreach_;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\Rector\Naming\Guard\BreakingVariableRenameGuard;
use Argtyper202511\Rector\Naming\Matcher\ForeachMatcher;
use Argtyper202511\Rector\Naming\Naming\ExpectedNameResolver;
use Argtyper202511\Rector\Naming\NamingConvention\NamingConventionAnalyzer;
use Argtyper202511\Rector\Naming\ValueObject\VariableAndCallForeach;
use Argtyper202511\Rector\Naming\VariableRenamer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\RenameForeachValueVariableToMatchMethodCallReturnTypeRectorTest
 */
final class RenameForeachValueVariableToMatchMethodCallReturnTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Guard\BreakingVariableRenameGuard
     */
    private $breakingVariableRenameGuard;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\ExpectedNameResolver
     */
    private $expectedNameResolver;
    /**
     * @readonly
     * @var \Rector\Naming\NamingConvention\NamingConventionAnalyzer
     */
    private $namingConventionAnalyzer;
    /**
     * @readonly
     * @var \Rector\Naming\VariableRenamer
     */
    private $variableRenamer;
    /**
     * @readonly
     * @var \Rector\Naming\Matcher\ForeachMatcher
     */
    private $foreachMatcher;
    /**
     * @var string[]
     */
    private const UNREADABLE_GENERIC_NAMES = ['traversable', 'iterable', 'generator', 'rewindableGenerator'];
    public function __construct(BreakingVariableRenameGuard $breakingVariableRenameGuard, ExpectedNameResolver $expectedNameResolver, NamingConventionAnalyzer $namingConventionAnalyzer, VariableRenamer $variableRenamer, ForeachMatcher $foreachMatcher)
    {
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
        $this->expectedNameResolver = $expectedNameResolver;
        $this->namingConventionAnalyzer = $namingConventionAnalyzer;
        $this->variableRenamer = $variableRenamer;
        $this->foreachMatcher = $foreachMatcher;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Renames value variable name in foreach loop to match method type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $array = [];
        foreach ($object->getMethods() as $property) {
            $array[] = $property;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $array = [];
        foreach ($object->getMethods() as $method) {
            $array[] = $method;
        }
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
        return [ClassMethod::class, Closure::class, Function_::class];
    }
    /**
     * @param ClassMethod|Closure|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $hasRenamed = \false;
        $this->traverseNodesWithCallable($node->stmts, function (Node $subNode) use($node, &$hasRenamed) : ?int {
            if ($subNode instanceof Class_ || $subNode instanceof Closure || $subNode instanceof Function_) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$subNode instanceof Foreach_) {
                return null;
            }
            $variableAndCallForeach = $this->foreachMatcher->match($subNode, $node);
            if (!$variableAndCallForeach instanceof VariableAndCallForeach) {
                return null;
            }
            $expectedName = $this->expectedNameResolver->resolveForForeach($variableAndCallForeach);
            if ($expectedName === null) {
                return null;
            }
            if ($this->isName($variableAndCallForeach->getVariable(), $expectedName)) {
                return null;
            }
            if ($this->shouldSkip($variableAndCallForeach, $expectedName)) {
                return null;
            }
            $hasChanged = $this->variableRenamer->renameVariableInFunctionLike($variableAndCallForeach->getFunctionLike(), $variableAndCallForeach->getVariableName(), $expectedName);
            // use different variable on purpose to avoid variable re-assign back to false
            // after go to other method
            if ($hasChanged) {
                $hasRenamed = \true;
            }
            return null;
        });
        if ($hasRenamed) {
            return $node;
        }
        return null;
    }
    private function shouldSkip(VariableAndCallForeach $variableAndCallForeach, string $expectedName) : bool
    {
        if (\in_array($expectedName, self::UNREADABLE_GENERIC_NAMES, \true)) {
            return \true;
        }
        if ($this->namingConventionAnalyzer->isCallMatchingVariableName($variableAndCallForeach->getCall(), $variableAndCallForeach->getVariableName(), $expectedName)) {
            return \true;
        }
        return $this->breakingVariableRenameGuard->shouldSkipVariable($variableAndCallForeach->getVariableName(), $expectedName, $variableAndCallForeach->getFunctionLike(), $variableAndCallForeach->getVariable());
    }
}
