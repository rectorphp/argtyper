<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php82\Rector\Encapsed;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Scalar\InterpolatedString;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php82\Rector\Encapsed\VariableInStringInterpolationFixerRector\VariableInStringInterpolationFixerRectorTest
 */
final class VariableInStringInterpolationFixerRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace deprecated `${var}` to `{$var}`', [new CodeSample(<<<'CODE_SAMPLE'
$c = "football";
echo "I like playing ${c}";
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$c = "football";
echo "I like playing {$c}";
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [InterpolatedString::class];
    }
    /**
     * @param InterpolatedString $node
     */
    public function refactor(Node $node) : ?Node
    {
        $oldTokens = $this->file->getOldTokens();
        $hasChanged = \false;
        foreach ($node->parts as $part) {
            if (!$part instanceof Variable && !($part instanceof ArrayDimFetch && $part->var instanceof Variable)) {
                continue;
            }
            $startTokenPos = $part->getStartTokenPos();
            if (!isset($oldTokens[$startTokenPos])) {
                continue;
            }
            if ((string) $oldTokens[$startTokenPos] !== '${') {
                continue;
            }
            if ($part instanceof Variable) {
                $part->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            } else {
                $oldTokens[$startTokenPos]->text = '{$';
            }
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_VARIABLE_IN_STRING_INTERPOLATION;
    }
}
