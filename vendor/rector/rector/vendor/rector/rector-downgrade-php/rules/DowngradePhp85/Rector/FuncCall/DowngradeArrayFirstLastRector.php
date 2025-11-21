<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp85\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://php.watch/versions/8.5/array_first-array_last
 * @see \Rector\Tests\DowngradePhp85\Rector\FuncCall\DowngradeArrayFirstLastRector\DowngradeArrayFirstLastRectorTest
 */
final class DowngradeArrayFirstLastRector extends AbstractRector
{
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace array_first() and array_last() with $array[array_key_first($array)] and $array[array_key_last($array)]', [new CodeSample(<<<'CODE_SAMPLE'
echo array_first($array);
echo array_last($array);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo $array[array_key_first($array)];
echo $array[array_key_last($array)];
CODE_SAMPLE
)]);
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isNames($node, ['array_first', 'array_last'])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        if (\count($args) !== 1) {
            return null;
        }
        $functionName = $this->isName($node, 'array_first') ? 'array_key_first' : 'array_key_last';
        return new ArrayDimFetch($args[0]->value, $this->nodeFactory->createFuncCall($functionName, [$args[0]->value]));
    }
}
