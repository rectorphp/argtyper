<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Renaming\Rector\Cast;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Cast;
use Argtyper202511\PhpParser\Node\Expr\Cast\Double;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Renaming\ValueObject\RenameCast;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\Cast\RenameCastRector\RenameCastRectorTest
 */
final class RenameCastRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<RenameCast>
     */
    private $renameCasts = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Renames casts', [new ConfiguredCodeSample('$real = (real) $real;', '$real = (float) $real;', [new RenameCast(Double::class, Double::KIND_REAL, Double::KIND_FLOAT)])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Cast::class];
    }
    /**
     * @param Cast $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($this->renameCasts as $renameCast) {
            $expectedClassName = $renameCast->getFromCastExprClass();
            if (!$node instanceof $expectedClassName) {
                continue;
            }
            if ($node->getAttribute(AttributeKey::KIND) !== $renameCast->getFromCastKind()) {
                continue;
            }
            $node->setAttribute(AttributeKey::KIND, $renameCast->getToCastKind());
            $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $node->setAttribute('startTokenPos', -1);
            $node->setAttribute('endTokenPos', -1);
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsInstanceOf($configuration, RenameCast::class);
        $this->renameCasts = $configuration;
    }
}
