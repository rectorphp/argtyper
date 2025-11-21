<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\FunctionLike;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as renamed to \Rector\DeadCode\Rector\FunctionLike\NarrowWideUnionReturnTypeRector
 */
final class NarrowTooWideReturnTypeRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Deprecated', []);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @return never
     */
    public function refactor(Node $node)
    {
        throw new ShouldNotHappenException(\sprintf('Class "%s" is deprecated and renamed to "%s". Use the new class instead.', self::class, \Argtyper202511\Rector\DeadCode\Rector\FunctionLike\NarrowWideUnionReturnTypeRector::class));
    }
}
