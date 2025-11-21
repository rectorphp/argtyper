<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php73\Rector\BooleanOr;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanOr;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\Php71\IsArrayAndDualCheckToAble;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\ValueObject\PolyfillPackage;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Rector\VersionBonding\Contract\RelatedPolyfillInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php73\Rector\BinaryOr\IsCountableRector\IsCountableRectorTest
 */
final class IsCountableRector extends AbstractRector implements MinPhpVersionInterface, RelatedPolyfillInterface
{
    /**
     * @readonly
     * @var \Rector\Php71\IsArrayAndDualCheckToAble
     */
    private $isArrayAndDualCheckToAble;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(IsArrayAndDualCheckToAble $isArrayAndDualCheckToAble, ReflectionProvider $reflectionProvider)
    {
        $this->isArrayAndDualCheckToAble = $isArrayAndDualCheckToAble;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes is_array + Countable check to is_countable', [new CodeSample(<<<'CODE_SAMPLE'
is_array($foo) || $foo instanceof Countable;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
is_countable($foo);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [BooleanOr::class];
    }
    /**
     * @param BooleanOr $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip()) {
            return null;
        }
        return $this->isArrayAndDualCheckToAble->processBooleanOr($node, 'Countable', 'is_countable');
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::IS_COUNTABLE;
    }
    public function providePolyfillPackage(): string
    {
        return PolyfillPackage::PHP_73;
    }
    private function shouldSkip(): bool
    {
        return !$this->reflectionProvider->hasFunction(new Name('is_countable'), null);
    }
}
