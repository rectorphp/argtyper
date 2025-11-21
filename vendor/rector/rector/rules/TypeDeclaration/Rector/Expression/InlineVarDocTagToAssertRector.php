<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\Expression;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as might create production-crashing code. Either use strict assert or proper type declarations instead.
 */
final class InlineVarDocTagToAssertRector extends AbstractRector implements MinPhpVersionInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert inline `@var` tags to calls to `assert()`', [new CodeSample(<<<'CODE_SAMPLE'
/** @var Foo $foo */
$foo = createFoo();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$foo = createFoo();
assert($foo instanceof Foo);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     * @return Node[]|null
     */
    public function refactor(Node $node): ?array
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated as it might create production-crashing code. Either use strict assert or proper type declarations instead.', self::class));
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::STRING_IN_ASSERT_ARG;
    }
}
