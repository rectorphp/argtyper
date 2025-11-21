<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\Concat;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Concat;
use Argtyper202511\PhpParser\Node\Expr\Cast\String_;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Concat\RemoveConcatAutocastRector\RemoveConcatAutocastRectorTest
 */
final class RemoveConcatAutocastRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove (string) casting when it comes to concat, that does this by default', [new CodeSample(<<<'CODE_SAMPLE'
class SomeConcatenatingClass
{
    public function run($value)
    {
        return 'hi ' . (string) $value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeConcatenatingClass
{
    public function run($value)
    {
        return 'hi ' . $value;
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
        return [Concat::class];
    }
    /**
     * @param Concat $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->left instanceof String_ && !$node->right instanceof String_) {
            return null;
        }
        $node->left = $this->removeStringCast($node->left);
        $node->right = $this->removeStringCast($node->right);
        return $node;
    }
    private function removeStringCast(Expr $expr) : Expr
    {
        if (!$expr instanceof String_) {
            return $expr;
        }
        $targetExpr = $expr->expr;
        $tokens = $this->file->getOldTokens();
        if ($expr->expr instanceof BinaryOp) {
            $castStartTokenPos = $expr->getStartTokenPos();
            $targetExprStartTokenPos = $targetExpr->getStartTokenPos();
            while (++$castStartTokenPos < $targetExprStartTokenPos) {
                if (isset($tokens[$castStartTokenPos]) && (string) $tokens[$castStartTokenPos] === '(') {
                    $targetExpr->setAttribute(AttributeKey::WRAPPED_IN_PARENTHESES, \true);
                    break;
                }
            }
        }
        return $targetExpr;
    }
}
