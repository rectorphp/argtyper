<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\Identical;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\Rector\NodeManipulator\BinaryOpManipulator;
use Argtyper202511\Rector\Php71\ValueObject\TwoNodeMatch;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\SimplifyArraySearchRector\SimplifyArraySearchRectorTest
 */
final class SimplifyArraySearchRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(BinaryOpManipulator $binaryOpManipulator, ValueResolver $valueResolver)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify array_search to in_array', [new CodeSample('array_search("searching", $array) !== false;', 'in_array("searching", $array);'), new CodeSample('array_search("searching", $array, true) !== false;', 'in_array("searching", $array, true);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Identical::class, NotIdentical::class];
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node) : ?Node
    {
        $twoNodeMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode($node, function (Node $node) : bool {
            if (!$node instanceof FuncCall) {
                return \false;
            }
            return $this->isName($node, 'array_search');
        }, function (Node $node) : bool {
            return $node instanceof Expr && $this->valueResolver->isFalse($node);
        });
        if (!$twoNodeMatch instanceof TwoNodeMatch) {
            return null;
        }
        /** @var FuncCall $funcCallExpr */
        $funcCallExpr = $twoNodeMatch->getFirstExpr();
        $inArrayFuncCall = $this->nodeFactory->createFuncCall('in_array', $funcCallExpr->args);
        if ($node instanceof Identical) {
            return new BooleanNot($inArrayFuncCall);
        }
        return $inArrayFuncCall;
    }
}
