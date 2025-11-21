<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp81\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/first_class_callable_syntax
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector\DowngradeFirstClassCallableSyntaxRectorTest
 */
final class DowngradeFirstClassCallableSyntaxRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace variadic placeholders usage by Closure::fromCallable()', [new CodeSample(<<<'CODE_SAMPLE'
$cb = strlen(...);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$cb = \Closure::fromCallable('strlen');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, MethodCall::class, StaticCall::class];
    }
    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?StaticCall
    {
        if (!$node->isFirstClassCallable()) {
            return null;
        }
        $callbackExpr = $this->createCallback($node);
        return $this->createClosureFromCallableCall($callbackExpr);
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     * @return \PhpParser\Node\Scalar\String_|\PhpParser\Node\Expr\Array_|\PhpParser\Node\Expr
     */
    private function createCallback($node)
    {
        if ($node instanceof FuncCall) {
            return $node->name instanceof Name ? new String_($node->name->toString()) : $node->name;
        }
        if ($node instanceof MethodCall) {
            $object = $node->var;
            $method = $node->name instanceof Identifier ? new String_($node->name->toString()) : $node->name;
            return new Array_([new ArrayItem($object), new ArrayItem($method)]);
        }
        // StaticCall
        $class = $node->class instanceof Name ? new ClassConstFetch($node->class, 'class') : $node->class;
        $method = $node->name instanceof Identifier ? new String_($node->name->toString()) : $node->name;
        return $this->nodeFactory->createArray([$class, $method]);
    }
    /**
     * @param \PhpParser\Node\Scalar\String_|\PhpParser\Node\Expr\Array_|\PhpParser\Node\Expr $expr
     */
    private function createClosureFromCallableCall($expr): StaticCall
    {
        return new StaticCall(new FullyQualified('Closure'), 'fromCallable', [new Arg($expr)]);
    }
}
