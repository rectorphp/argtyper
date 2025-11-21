<?php

declare (strict_types=1);
namespace Argtyper202511\PhpParser;

/**
 * @codeCoverageIgnore
 */
abstract class NodeVisitorAbstract implements \Argtyper202511\PhpParser\NodeVisitor
{
    public function beforeTraverse(array $nodes)
    {
        return null;
    }
    public function enterNode(\Argtyper202511\PhpParser\Node $node)
    {
        return null;
    }
    public function leaveNode(\Argtyper202511\PhpParser\Node $node)
    {
        return null;
    }
    public function afterTraverse(array $nodes)
    {
        return null;
    }
}
