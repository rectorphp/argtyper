<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\Rector\ArrowFunction;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\Rector\CodingStyle\Guard\StaticGuard;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector\StaticArrowFunctionRectorTest
 */
final class StaticArrowFunctionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\Guard\StaticGuard
     */
    private $staticGuard;
    public function __construct(StaticGuard $staticGuard)
    {
        $this->staticGuard = $staticGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes ArrowFunction to be static when possible', [new CodeSample(<<<'CODE_SAMPLE'
fn (): string => 'test';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
static fn (): string => 'test';
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ArrowFunction::class];
    }
    /**
     * @param ArrowFunction $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->staticGuard->isLegal($node)) {
            return null;
        }
        $node->static = \true;
        return $node;
    }
}
