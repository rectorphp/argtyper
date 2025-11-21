<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Contract\Rector;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\NodeVisitor;
interface RectorInterface extends NodeVisitor
{
    /**
     * List of nodes this class checks, classes that implements \PhpParser\Node
     * See beautiful map of all nodes https://github.com/rectorphp/php-parser-nodes-docs#node-overview
     *
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array;
    /**
     * Process Node of matched type
     * @return Node|Node[]|null|NodeVisitor::*
     */
    public function refactor(Node $node);
}
