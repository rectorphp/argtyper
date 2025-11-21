<?php

declare (strict_types=1);
namespace Argtyper202511\PhpParser;

interface Builder
{
    /**
     * Returns the built node.
     *
     * @return Node The built node
     */
    public function getNode() : \Argtyper202511\PhpParser\Node;
}
