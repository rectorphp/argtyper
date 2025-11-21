<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as adds dynamic attribute everywhere blindly or by name (blindly without checking need). Instead, aim at objects without dynamic properties, or use custom rule.
 */
final class AddAllowDynamicPropertiesAttributeRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add the `AllowDynamicProperties` attribute to all classes', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
namespace Example\Domain;

class SomeObject {
    public string $someProperty = 'hello world';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
namespace Example\Domain;

#[AllowDynamicProperties]
class SomeObject {
    public string $someProperty = 'hello world';
}
CODE_SAMPLE
, ['Example\\*'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    public function configure(array $configuration) : void
    {
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        throw new ShouldNotHappenException(\sprintf('%s is deprecated as it adds dynamic attribute everywhere blindly or by name (blindly without checking need). Instead, aim at objects without dynamic properties, or use custom rule.', self::class));
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_DYNAMIC_PROPERTIES;
    }
}
