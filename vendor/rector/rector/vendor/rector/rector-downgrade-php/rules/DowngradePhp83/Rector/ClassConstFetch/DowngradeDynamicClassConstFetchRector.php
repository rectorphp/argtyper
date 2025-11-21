<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp83\Rector\ClassConstFetch;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Concat;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/dynamic_class_constant_fetch
 *
 * @see \Rector\Tests\DowngradePhp83\Rector\ClassConstFetch\DowngradeDynamicClassConstFetchRector\DowngradeDynamicClassConstFetchRectorTest
 */
final class DowngradeDynamicClassConstFetchRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassConstFetch::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change dynamic class const fetch Example::{$constName} to constant(Example::class . \'::\' . $constName)', [new CodeSample(<<<'CODE_SAMPLE'
$value = Example::{$constName};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = constant(Example::class . '::' . $constName);
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->name instanceof Identifier) {
            return null;
        }
        return $this->nodeFactory->createFuncCall('constant', [new Concat(new Concat(new ClassConstFetch($node->class, new Identifier('class')), new String_('::')), $node->name)]);
    }
}
