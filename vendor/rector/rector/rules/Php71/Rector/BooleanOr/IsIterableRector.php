<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php71\Rector\BooleanOr;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanOr;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\Php71\IsArrayAndDualCheckToAble;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php71\Rector\BooleanOr\IsIterableRector\IsIterableRectorTest
 */
final class IsIterableRector extends AbstractRector implements MinPhpVersionInterface
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
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(IsArrayAndDualCheckToAble $isArrayAndDualCheckToAble, ReflectionProvider $reflectionProvider, PhpVersionProvider $phpVersionProvider)
    {
        $this->isArrayAndDualCheckToAble = $isArrayAndDualCheckToAble;
        $this->reflectionProvider = $reflectionProvider;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::IS_ITERABLE;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes is_array + Traversable check to is_iterable', [new CodeSample('is_array($foo) || $foo instanceof Traversable;', 'is_iterable($foo);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [BooleanOr::class];
    }
    /**
     * @param BooleanOr $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip()) {
            return null;
        }
        return $this->isArrayAndDualCheckToAble->processBooleanOr($node, 'Traversable', 'is_iterable');
    }
    private function shouldSkip() : bool
    {
        if ($this->reflectionProvider->hasFunction(new Name('is_iterable'), null)) {
            return \false;
        }
        return !$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::IS_ITERABLE);
    }
}
