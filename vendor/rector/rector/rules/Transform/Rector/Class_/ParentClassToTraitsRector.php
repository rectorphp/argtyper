<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Transform\ValueObject\ParentClassToTraits;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as very narrow use case to be generic rule. Create custom rule instead.
 */
final class ParentClassToTraitsRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace parent class to specific traits', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass extends Nette\Object
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    use Nette\SmartObject;
}
CODE_SAMPLE
, [new ParentClassToTraits('Argtyper202511\\Nette\\Object', ['Argtyper202511\\Nette\\SmartObject'])])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        throw new ShouldNotHappenException(\sprintf('"%s" rule is deprecated as very narrow use case to be generic rule. Create custom rule instead', self::class));
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
    }
}
