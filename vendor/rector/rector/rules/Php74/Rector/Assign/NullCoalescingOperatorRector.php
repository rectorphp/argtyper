<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php74\Rector\Assign;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\AssignOp\Coalesce as AssignCoalesce;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Coalesce;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php74\Rector\Assign\NullCoalescingOperatorRector\NullCoalescingOperatorRectorTest
 */
final class NullCoalescingOperatorRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use null coalescing operator `??=`', [new CodeSample(<<<'CODE_SAMPLE'
$array = [];
$array['user_id'] = $array['user_id'] ?? 'value';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$array = [];
$array['user_id'] ??= 'value';
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(Node $node) : ?AssignCoalesce
    {
        if (!$node->expr instanceof Coalesce) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($node->var, $node->expr->left)) {
            return null;
        }
        return new AssignCoalesce($node->var, $node->expr->right);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULL_COALESCE_ASSIGN;
    }
}
