<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Closure;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Rector\CodingStyle\Guard\StaticGuard;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Closure\StaticClosureRector\StaticClosureRectorTest
 */
final class StaticClosureRector extends AbstractRector
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
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes Closure to be static when possible', [new CodeSample(<<<'CODE_SAMPLE'
function () {
    if (rand(0, 1)) {
        return 1;
    }

    return 2;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
static function () {
    if (rand(0, 1)) {
        return 1;
    }

    return 2;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->staticGuard->isLegal($node)) {
            return null;
        }
        $node->static = \true;
        return $node;
    }
}
