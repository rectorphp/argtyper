<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as this rule was part of removed doctrine service set. It does not work as standalone. Create custom rule instead.
 */
final class ReplaceParentCallByPropertyCallRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes method calls in child of specific types to defined property method call', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(SomeTypeToReplace $someTypeToReplace)
    {
        $someTypeToReplace->someMethodCall();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(SomeTypeToReplace $someTypeToReplace)
    {
        $this->someProperty->someMethodCall();
    }
}
CODE_SAMPLE
, [new ReplaceParentCallByPropertyCall('SomeTypeToReplace', 'someMethodCall', 'someProperty')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        throw new ShouldNotHappenException(\sprintf('This Rector "%s" is deprecated as it was part of removed doctrine service set. It does not work as standalone. Create custom rule instead.', self::class));
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
    }
}
