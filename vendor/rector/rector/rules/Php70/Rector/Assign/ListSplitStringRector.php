<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php70\Rector\Assign;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\List_;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php70\Rector\Assign\ListSplitStringRector\ListSplitStringRectorTest
 */
final class ListSplitStringRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('list() cannot split string directly anymore, use str_split()', [new CodeSample('list($foo) = "string";', 'list($foo) = str_split("string");')]);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NO_LIST_SPLIT_STRING;
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
    public function refactor(Node $node) : ?Node
    {
        if (!$node->var instanceof List_) {
            return null;
        }
        $exprType = $this->getType($node->expr);
        if (!$exprType->isString()->yes()) {
            return null;
        }
        $node->expr = $this->nodeFactory->createFuncCall('str_split', [$node->expr]);
        return $node;
    }
}
