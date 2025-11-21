<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\PHPUnit100\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\TraitUse;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/sebastianbergmann/phpunit/issues/4142
 * @changelog https://github.com/sebastianbergmann/phpunit/issues/4141
 * @changelog https://github.com/sebastianbergmann/phpunit/issues/4149
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit100\Rector\Class_\AddProphecyTraitRector\AddProphecyTraitRectorTest
 */
final class AddProphecyTraitRector extends AbstractRector
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
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var string
     */
    private const PROPHECY_TRAIT = 'Argtyper202511\\Prophecy\\PhpUnit\\ProphecyTrait';
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ReflectionResolver $reflectionResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add Prophecy trait for method using $this->prophesize()', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public function testOne(): void
    {
        $prophecy = $this->prophesize(\AnInterface::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class ExampleTest extends TestCase
{
    use ProphecyTrait;

    public function testOne(): void
    {
        $prophecy = $this->prophesize(\AnInterface::class);
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
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $traitUse = new TraitUse([new FullyQualified(self::PROPHECY_TRAIT)]);
        $node->stmts = \array_merge([$traitUse], $node->stmts);
        return $node;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        $hasProphesizeMethodCall = (bool) $this->betterNodeFinder->findFirst($class, function (Node $node) : bool {
            return $this->testsNodeAnalyzer->isAssertMethodCallName($node, 'prophesize');
        });
        if (!$hasProphesizeMethodCall) {
            return \true;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($class);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->hasTraitUse(self::PROPHECY_TRAIT);
    }
}
