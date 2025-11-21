<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Strict\Rector\If_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated this rule is risky and requires manual checking
 */
final class BooleanInIfConditionRuleFixerRector extends AbstractRector implements DeprecatedInterface
{
    public const TREAT_AS_NON_EMPTY = 'treat_as_non_empty';
    public function getRuleDefinition() : RuleDefinition
    {
        $errorMessage = \sprintf('Fixer for PHPStan reports by strict type rule - "%s"', 'Argtyper202511\\PHPStan\\Rules\\BooleansInConditions\\BooleanInIfConditionRule');
        return new RuleDefinition($errorMessage, [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class NegatedString
{
    public function run(string $name)
    {
        if ($name) {
            return 'name';
        }

        return 'no name';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class NegatedString
{
    public function run(string $name)
    {
        if ($name !== '') {
            return 'name';
        }

        return 'no name';
    }
}
CODE_SAMPLE
, [self::TREAT_AS_NON_EMPTY => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node) : ?If_
    {
        throw new ShouldNotHappenException('This rule is deprecated and will be removed as risky and requires manual checking');
    }
}
