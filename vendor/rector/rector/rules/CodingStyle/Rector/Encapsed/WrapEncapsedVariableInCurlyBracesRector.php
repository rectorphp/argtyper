<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\Rector\Encapsed;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Scalar\InterpolatedString;
use Argtyper202511\PhpParser\Token;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector\WrapEncapsedVariableInCurlyBracesRectorTest
 */
final class WrapEncapsedVariableInCurlyBracesRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Wrap encapsed variables in curly braces', [new CodeSample(<<<'CODE_SAMPLE'
function run($world)
{
    echo "Hello $world!";
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run($world)
{
    echo "Hello {$world}!";
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [InterpolatedString::class];
    }
    /**
     * @param InterpolatedString $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasVariableBeenWrapped = \false;
        $oldTokens = $this->file->getOldTokens();
        foreach ($node->parts as $index => $nodePart) {
            if ($nodePart instanceof Variable && $nodePart->getStartTokenPos() >= 0) {
                $start = $oldTokens[$nodePart->getStartTokenPos() - 1] ?? null;
                $end = $oldTokens[$nodePart->getEndTokenPos() + 1] ?? null;
                if ($start instanceof Token && $end instanceof Token && $start->text === '{' && $end->text === '}') {
                    continue;
                }
                $hasVariableBeenWrapped = \true;
                $node->parts[$index] = new Variable($nodePart->name);
            }
        }
        if (!$hasVariableBeenWrapped) {
            return null;
        }
        return $node;
    }
}
