<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php74\Rector\Double;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Cast\Double;
use Argtyper202511\Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Renaming\Rector\Cast\RenameCastRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Use same but configurable `Rector\Renaming\Rector\Cast\RenameCastRector` with configuration instead
 */
final class RealToFloatTypeCastRector extends AbstractRector implements MinPhpVersionInterface, DeprecatedInterface
{
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATE_REAL;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change deprecated `(real)` to `(float)`', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $number = (real) 5;
        $number = (float) 5;
        $number = (double) 5;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $number = (float) 5;
        $number = (float) 5;
        $number = (double) 5;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Double::class];
    }
    /**
     * @param Double $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('This rule is deprecated. Use configurable "%s" rule instead.', RenameCastRector::class));
    }
}
