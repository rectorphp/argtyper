<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\PHPUnit100\Rector\Class_;

use Argtyper202511\PhpParser\Modifiers;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/sebastianbergmann/phpunit/issues/3975
 * @see https://github.com/sebastianbergmann/phpunit/commit/705874f1b867fd99865e43cb5eaea4e6d141582f
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit100\Rector\Class_\ParentTestClassConstructorRector\ParentTestClassConstructorRectorTest
 */
final class ParentTestClassConstructorRector extends AbstractRector
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
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('PHPUnit\\Framework\\TestCase requires a parent constructor call', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeHelper extends TestCase
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeHelper extends TestCase
{
    public function __construct()
    {
        parent::__construct(static::class);
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
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        // it already has a constructor, skip as it might require specific tweaking
        if ($node->getMethod(MethodName::CONSTRUCT)) {
            return null;
        }
        $constructorClassMethod = new ClassMethod(MethodName::CONSTRUCT);
        $constructorClassMethod->flags |= Modifiers::PUBLIC;
        $constructorClassMethod->stmts[] = new Expression($this->createParentConstructorCall());
        $node->stmts = \array_merge([$constructorClassMethod], $node->stmts);
        return $node;
    }
    private function createParentConstructorCall() : StaticCall
    {
        $staticClassConstFetch = new ClassConstFetch(new Name('static'), 'class');
        return new StaticCall(new Name('parent'), MethodName::CONSTRUCT, [new Arg($staticClassConstFetch)]);
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        if ($class->isAbstract()) {
            return \true;
        }
        if ($class->isAnonymous()) {
            return \true;
        }
        $className = $this->getName($class);
        // loaded automatically by PHPUnit
        if (\substr_compare((string) $className, 'Test', -\strlen('Test')) === 0) {
            return \true;
        }
        if (\substr_compare((string) $className, 'TestCase', -\strlen('TestCase')) === 0) {
            return \true;
        }
        return (bool) $class->getAttribute('hasRemovedFinalConstruct');
    }
}
