<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp80\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/str_contains
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeStrContainsRector\DowngradeStrContainsRectorTest
 */
final class DowngradeStrContainsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace str_contains() with strpos() !== false', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return str_contains('abc', 'a');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return strpos('abc', 'a') !== false;
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
        return [FuncCall::class, BooleanNot::class];
    }
    /**
     * @param FuncCall|BooleanNot $node
     * @return Identical|NotIdentical|null The refactored node.
     */
    public function refactor(Node $node)
    {
        $funcCall = $this->matchStrContainsOrNotStrContains($node);
        if (!$funcCall instanceof FuncCall) {
            return null;
        }
        $args = $funcCall->getArgs();
        if (count($args) < 2) {
            return null;
        }
        $haystack = $args[0]->value;
        $needle = $args[1]->value;
        $funcCall = $this->nodeFactory->createFuncCall('strpos', [$haystack, $needle]);
        if ($node instanceof BooleanNot) {
            return new Identical($funcCall, $this->nodeFactory->createFalse());
        }
        return new NotIdentical($funcCall, $this->nodeFactory->createFalse());
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\BooleanNot $expr
     */
    private function matchStrContainsOrNotStrContains($expr): ?FuncCall
    {
        $expr = $expr instanceof BooleanNot ? $expr->expr : $expr;
        if (!$expr instanceof FuncCall) {
            return null;
        }
        if (!$this->isName($expr, 'str_contains')) {
            return null;
        }
        return $expr;
    }
}
