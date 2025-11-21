<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector\DowngradeArrayMergeCallWithoutArgumentsRectorTest
 */
final class DowngradeArrayMergeCallWithoutArgumentsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add missing param to `array_merge` and `array_merge_recursive`', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        array_merge();
        array_merge_recursive();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        array_merge([]);
        array_merge_recursive([]);
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->shouldRefactor($node)) {
            return null;
        }
        $node->args = [new Arg(new Array_())];
        return $node;
    }
    private function shouldRefactor(FuncCall $funcCall): bool
    {
        if (!$this->isNames($funcCall, ['array_merge', 'array_merge_recursive'])) {
            return \false;
        }
        // If param is provided, do nothing
        return $funcCall->args === [];
    }
}
