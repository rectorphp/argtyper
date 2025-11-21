<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Strict\Rector\BooleanNot;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as risky and requires manual checking
 */
final class BooleanInBooleanNotRuleFixerRector extends AbstractRector implements DeprecatedInterface
{
    public const TREAT_AS_NON_EMPTY = 'treat_as_non_empty';
    public function getRuleDefinition(): RuleDefinition
    {
        $errorMessage = \sprintf('Fixer for PHPStan reports by strict type rule - "%s"', 'Argtyper202511\PHPStan\Rules\BooleansInConditions\BooleanInBooleanNotRule');
        return new RuleDefinition($errorMessage, [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(string|null $name)
    {
        if (! $name) {
            return 'no name';
        }

        return 'name';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(string|null $name)
    {
        if ($name === null) {
            return 'no name';
        }

        return 'name';
    }
}
CODE_SAMPLE
, [self::TREAT_AS_NON_EMPTY => \true])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [BooleanNot::class];
    }
    /**
     * @param BooleanNot $node
     */
    public function refactor(Node $node): ?Expr
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated as risky and not practical', self::class));
    }
}
