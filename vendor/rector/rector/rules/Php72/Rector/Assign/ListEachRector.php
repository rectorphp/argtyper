<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php72\Rector\Assign;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\NodeManipulator\AssignManipulator;
use Argtyper202511\Rector\Php72\ValueObject\ListAndEach;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php72\Rector\Assign\ListEachRector\ListEachRectorTest
 */
final class ListEachRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\AssignManipulator
     */
    private $assignManipulator;
    public function __construct(AssignManipulator $assignManipulator)
    {
        $this->assignManipulator = $assignManipulator;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATE_EACH;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('each() function is deprecated, use key() and current() instead', [new CodeSample(<<<'CODE_SAMPLE'
list($key, $callback) = each($callbacks);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$key = key($callbacks);
$callback = current($callbacks);
next($callbacks);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     * @return null|Expression|Stmt[]
     */
    public function refactor(Node $node)
    {
        if (!$node->expr instanceof Assign) {
            return null;
        }
        $listAndEach = $this->assignManipulator->matchListAndEach($node->expr);
        if (!$listAndEach instanceof ListAndEach) {
            return null;
        }
        if ($this->shouldSkipAssign($listAndEach)) {
            return null;
        }
        $list = $listAndEach->getList();
        $eachFuncCall = $listAndEach->getEachFuncCall();
        // only key: list($key, ) = each($values);
        if ($list->items[0] instanceof ArrayItem && !$list->items[1] instanceof ArrayItem) {
            $keyFuncCall = $this->nodeFactory->createFuncCall('key', $eachFuncCall->args);
            $keyFuncCallAssign = new Assign($list->items[0]->value, $keyFuncCall);
            return new Expression($keyFuncCallAssign);
        }
        // only value: list(, $value) = each($values);
        if ($list->items[1] instanceof ArrayItem && !$list->items[0] instanceof ArrayItem) {
            $nextFuncCall = $this->nodeFactory->createFuncCall('next', $eachFuncCall->args);
            $currentFuncCall = $this->nodeFactory->createFuncCall('current', $eachFuncCall->args);
            $secondArrayItem = $list->items[1];
            $currentAssign = new Assign($secondArrayItem->value, $currentFuncCall);
            return [new Expression($currentAssign), new Expression($nextFuncCall)];
        }
        // both: list($key, $value) = each($values);
        $currentFuncCall = $this->nodeFactory->createFuncCall('current', $eachFuncCall->args);
        $secondArrayItem = $list->items[1];
        if (!$secondArrayItem instanceof ArrayItem) {
            throw new ShouldNotHappenException();
        }
        $currentAssign = new Assign($secondArrayItem->value, $currentFuncCall);
        $nextFuncCall = $this->nodeFactory->createFuncCall('next', $eachFuncCall->args);
        $keyFuncCall = $this->nodeFactory->createFuncCall('key', $eachFuncCall->args);
        $firstArrayItem = $list->items[0];
        if (!$firstArrayItem instanceof ArrayItem) {
            throw new ShouldNotHappenException();
        }
        $keyAssign = new Assign($firstArrayItem->value, $keyFuncCall);
        return [new Expression($keyAssign), new Expression($currentAssign), new Expression($nextFuncCall)];
    }
    private function shouldSkipAssign(ListAndEach $listAndEach): bool
    {
        $list = $listAndEach->getList();
        if (count($list->items) !== 2) {
            return \true;
        }
        // empty list → cannot handle
        if ($list->items[0] instanceof ArrayItem) {
            return \false;
        }
        return !$list->items[1] instanceof ArrayItem;
    }
}
