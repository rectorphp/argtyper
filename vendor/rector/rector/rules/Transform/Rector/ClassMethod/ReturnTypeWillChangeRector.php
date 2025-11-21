<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Interface_;
use Argtyper202511\Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Transform\ValueObject\ClassMethodReference;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as not used in any set, and discourages from type coverage and leads to worse code. Use type declaration set instead, to actually increase type coverage.
 */
final class ReturnTypeWillChangeRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add #[\\ReturnTypeWillChange] attribute to configured instanceof class with methods', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass implements ArrayAccess
{
    public function offsetGet($offset)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass implements ArrayAccess
{
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
    }
}
CODE_SAMPLE
, [new ClassMethodReference('ArrayAccess', 'offsetGet')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class, Interface_::class];
    }
    /**
     * @param Class_|Interface_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        throw new ShouldNotHappenException(\sprintf('Rule "%s" is deprecated as not used in any set, and discourages from type coverage and leads to worse code. Use type declaration set instead, to actually increase type coverage.', self::class));
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::RETURN_TYPE_WILL_CHANGE_ATTRIBUTE;
    }
}
