<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\BooleanNot;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Replace negated boolean literals with their simplified equivalents
 *
 * @see \Rector\Tests\CodeQuality\Rector\BooleanNot\ReplaceConstantBooleanNotRector\ReplaceConstantBooleanNotRectorTest
 */
final class ReplaceConstantBooleanNotRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace negated boolean literals (!false, !true) with their simplified equivalents (true, false)', [new CodeSample(<<<'CODE_SAMPLE'
if (!false) {
    return 'always true';
}

if (!true) {
    return 'never reached';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
if (true) {
    return 'always true';
}

if (false) {
    return 'never reached';
}
CODE_SAMPLE
)]);
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
    public function refactor(Node $node): ?Node
    {
        if ($this->valueResolver->isFalse($node->expr)) {
            return new ConstFetch(new Name('true'));
        }
        if ($this->valueResolver->isTrue($node->expr)) {
            return new ConstFetch(new Name('false'));
        }
        return null;
    }
}
