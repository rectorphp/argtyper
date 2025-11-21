<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpParser\NodeTraverser;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Namespace_;
use Argtyper202511\PhpParser\NodeTraverser;
use Argtyper202511\Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
final class FileWithoutNamespaceNodeTraverser extends NodeTraverser
{
    /**
     * @template TNode as Node\Stmt
     *
     * @param TNode[] $nodes
     * @return TNode[]|FileWithoutNamespace[]
     */
    public function traverse(array $nodes) : array
    {
        foreach ($nodes as $node) {
            if ($node instanceof Namespace_) {
                return $nodes;
            }
        }
        $fileWithoutNamespace = new FileWithoutNamespace($nodes);
        return [$fileWithoutNamespace];
    }
}
