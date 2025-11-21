<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php70\Rector\Switch_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Stmt\Switch_;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php70\Rector\Switch_\ReduceMultipleDefaultSwitchRector\ReduceMultipleDefaultSwitchRectorTest
 */
final class ReduceMultipleDefaultSwitchRector extends AbstractRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::NO_MULTIPLE_DEFAULT_SWITCH;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove first default switch, that is ignored', [new CodeSample(<<<'CODE_SAMPLE'
switch ($expr) {
    default:
         echo "Hello World";

    default:
         echo "Goodbye Moon!";
         break;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
switch ($expr) {
    default:
         echo "Goodbye Moon!";
         break;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Switch_::class];
    }
    /**
     * @param Switch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $defaultCases = [];
        foreach ($node->cases as $key => $case) {
            if ($case->cond instanceof Expr) {
                continue;
            }
            $defaultCases[$key] = $case;
        }
        $defaultCaseCount = count($defaultCases);
        if ($defaultCaseCount < 2) {
            return null;
        }
        foreach ($node->cases as $key => $case) {
            if ($case->cond instanceof Expr) {
                continue;
            }
            // remove previous default cases
            if ($defaultCaseCount > 1) {
                unset($node->cases[$key]);
                --$defaultCaseCount;
            }
        }
        return $node;
    }
}
