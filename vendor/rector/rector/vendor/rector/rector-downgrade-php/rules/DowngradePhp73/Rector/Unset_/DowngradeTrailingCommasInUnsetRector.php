<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp73\Rector\Unset_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Unset_;
use Argtyper202511\Rector\DowngradePhp73\Tokenizer\FollowedByCommaAnalyzer;
use Argtyper202511\Rector\DowngradePhp73\Tokenizer\TrailingCommaRemover;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp73\Rector\Unset_\DowngradeTrailingCommasInUnsetRector\DowngradeTrailingCommasInUnsetRectorTest
 */
final class DowngradeTrailingCommasInUnsetRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DowngradePhp73\Tokenizer\FollowedByCommaAnalyzer
     */
    private $followedByCommaAnalyzer;
    /**
     * @readonly
     * @var \Rector\DowngradePhp73\Tokenizer\TrailingCommaRemover
     */
    private $trailingCommaRemover;
    public function __construct(FollowedByCommaAnalyzer $followedByCommaAnalyzer, TrailingCommaRemover $trailingCommaRemover)
    {
        $this->followedByCommaAnalyzer = $followedByCommaAnalyzer;
        $this->trailingCommaRemover = $trailingCommaRemover;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove trailing commas in unset', [new CodeSample(<<<'CODE_SAMPLE'
unset(
	$x,
	$y,
);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
unset(
	$x,
	$y
);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Unset_::class];
    }
    /**
     * @param Unset_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->vars !== []) {
            \end($node->vars);
            $lastArgumentPosition = \key($node->vars);
            \reset($node->vars);
            $last = $node->vars[$lastArgumentPosition];
            if (!$this->followedByCommaAnalyzer->isFollowed($this->file, $last)) {
                return null;
            }
            $this->trailingCommaRemover->remove($this->file, $last);
            return $node;
        }
        return null;
    }
}
