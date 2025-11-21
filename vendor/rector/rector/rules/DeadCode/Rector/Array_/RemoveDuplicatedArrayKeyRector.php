<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\Array_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\PreDec;
use Argtyper202511\PhpParser\Node\Expr\PreInc;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\Rector\NodeAnalyzer\ExprAnalyzer;
use Argtyper202511\Rector\PhpParser\Printer\BetterStandardPrinter;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector\RemoveDuplicatedArrayKeyRectorTest
 */
final class RemoveDuplicatedArrayKeyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(BetterStandardPrinter $betterStandardPrinter, ExprAnalyzer $exprAnalyzer)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove duplicated key in defined arrays', [new CodeSample(<<<'CODE_SAMPLE'
$item = [
    1 => 'A',
    1 => 'B'
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$item = [
    1 => 'B'
];
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Array_::class];
    }
    /**
     * @param Array_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $duplicatedKeysArrayItems = $this->resolveDuplicateKeysArrayItems($node);
        if ($duplicatedKeysArrayItems === []) {
            return null;
        }
        foreach ($node->items as $key => $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$this->isArrayItemDuplicated($duplicatedKeysArrayItems, $arrayItem)) {
                continue;
            }
            unset($node->items[$key]);
        }
        return $node;
    }
    /**
     * @return ArrayItem[]
     */
    private function resolveDuplicateKeysArrayItems(Array_ $array): array
    {
        $arrayItemsByKeys = [];
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$arrayItem->key instanceof Expr) {
                continue;
            }
            // local variable is mostly fine, other dynamic, just skip
            if (!$arrayItem->key instanceof Variable && $this->exprAnalyzer->isDynamicExpr($arrayItem->key)) {
                continue;
            }
            $keyValue = $this->betterStandardPrinter->print($arrayItem->key);
            $arrayItemsByKeys[$keyValue][] = $arrayItem;
        }
        return $this->filterItemsWithSameKey($arrayItemsByKeys);
    }
    /**
     * @param array<mixed, ArrayItem[]> $arrayItemsByKeys
     * @return array<ArrayItem>
     */
    private function filterItemsWithSameKey(array $arrayItemsByKeys): array
    {
        $duplicatedArrayItems = [];
        foreach ($arrayItemsByKeys as $arrayItems) {
            if (count($arrayItems) <= 1) {
                continue;
            }
            $currentArrayItem = current($arrayItems);
            /** @var Expr $currentArrayItemKey */
            $currentArrayItemKey = $currentArrayItem->key;
            if ($currentArrayItemKey instanceof PreInc) {
                continue;
            }
            if ($currentArrayItemKey instanceof PreDec) {
                continue;
            }
            // keep last one
            array_pop($arrayItems);
            $duplicatedArrayItems = array_merge($duplicatedArrayItems, $arrayItems);
        }
        return $duplicatedArrayItems;
    }
    /**
     * @param ArrayItem[] $duplicatedKeysArrayItems
     */
    private function isArrayItemDuplicated(array $duplicatedKeysArrayItems, ArrayItem $arrayItem): bool
    {
        return in_array($arrayItem, $duplicatedKeysArrayItems, \true);
    }
}
