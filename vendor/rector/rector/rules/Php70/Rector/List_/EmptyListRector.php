<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php70\Rector\List_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr\List_;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php70\Rector\List_\EmptyListRector\EmptyListRectorTest
 */
final class EmptyListRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('list() cannot be empty', [new CodeSample(<<<'CODE_SAMPLE'
'list() = $values;'
CODE_SAMPLE
, <<<'CODE_SAMPLE'
'list($unusedGenerated) = $values;'
CODE_SAMPLE
)]);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NO_EMPTY_LIST;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [List_::class];
    }
    /**
     * @param List_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($node->items as $item) {
            if ($item instanceof ArrayItem) {
                return null;
            }
        }
        $node->items[0] = new ArrayItem(new Variable('unusedGenerated'));
        return $node;
    }
}
