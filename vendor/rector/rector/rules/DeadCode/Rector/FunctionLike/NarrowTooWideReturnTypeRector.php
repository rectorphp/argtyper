<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\FunctionLike;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as renamed to \Rector\DeadCode\Rector\FunctionLike\NarrowWideUnionReturnTypeRector
 */
final class NarrowTooWideReturnTypeRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Deprecated', []);
    }
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @return never
     */
    public function refactor(Node $node)
    {
        throw new ShouldNotHappenException(sprintf('Class "%s" is deprecated and renamed to "%s". Use the new class instead.', self::class, \Rector\DeadCode\Rector\FunctionLike\NarrowWideUnionReturnTypeRector::class));
    }
}
