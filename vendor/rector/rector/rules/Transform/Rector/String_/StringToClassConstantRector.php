<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\Rector\String_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Transform\ValueObject\StringToClassConstant;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\String_\StringToClassConstantRector\StringToClassConstantRectorTest
 */
final class StringToClassConstantRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var StringToClassConstant[]
     */
    private $stringsToClassConstants = [];
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change strings to specific constants', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return ['compiler.post_dump' => 'compile'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return [\Yet\AnotherClass::CONSTANT => 'compile'];
    }
}
CODE_SAMPLE
, [new StringToClassConstant('compiler.post_dump', 'Argtyper202511\\Yet\\AnotherClass', 'CONSTANT')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [String_::class];
    }
    /**
     * @param String_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($this->stringsToClassConstants as $stringToClassConstant) {
            if (!$this->valueResolver->isValue($node, $stringToClassConstant->getString())) {
                continue;
            }
            return $this->nodeFactory->createClassConstFetch($stringToClassConstant->getClass(), $stringToClassConstant->getConstant());
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, StringToClassConstant::class);
        $this->stringsToClassConstants = $configuration;
    }
}
