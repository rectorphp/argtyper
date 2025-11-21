<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver;

use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\NodeTraverser;
use Argtyper202511\PhpParser\NodeVisitor\CloningVisitor;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
use Argtyper202511\Rector\PhpParser\NodeTraverser\FileWithoutNamespaceNodeTraverser;
final class NodeScopeAndMetadataDecorator
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver
     */
    private $phpStanNodeScopeResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\NodeTraverser\FileWithoutNamespaceNodeTraverser
     */
    private $fileWithoutNamespaceNodeTraverser;
    /**
     * @readonly
     * @var \PhpParser\NodeTraverser
     */
    private $nodeTraverser;
    public function __construct(CloningVisitor $cloningVisitor, PHPStanNodeScopeResolver $phpStanNodeScopeResolver, FileWithoutNamespaceNodeTraverser $fileWithoutNamespaceNodeTraverser)
    {
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->fileWithoutNamespaceNodeTraverser = $fileWithoutNamespaceNodeTraverser;
        // needed for format preserving printing
        $this->nodeTraverser = new NodeTraverser($cloningVisitor);
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function decorateNodesFromFile(string $filePath, array $stmts): array
    {
        $stmts = $this->fileWithoutNamespaceNodeTraverser->traverse($stmts);
        $stmts = $this->phpStanNodeScopeResolver->processNodes($stmts, $filePath);
        return $this->nodeTraverser->traverse($stmts);
    }
}
