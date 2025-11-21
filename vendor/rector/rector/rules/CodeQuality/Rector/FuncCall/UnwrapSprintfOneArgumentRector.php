<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector\UnwrapSprintfOneArgumentRectorTest
 */
final class UnwrapSprintfOneArgumentRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Unwrap `sprintf()` with one argument', [new CodeSample(<<<'CODE_SAMPLE'
echo sprintf('value');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo 'value';
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node, 'sprintf')) {
            return null;
        }
        if (\count($node->getArgs()) > 1) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        if ($firstArg->unpack) {
            return null;
        }
        return $firstArg->value;
    }
}
