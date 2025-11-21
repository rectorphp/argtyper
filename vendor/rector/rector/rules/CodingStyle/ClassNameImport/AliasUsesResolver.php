<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\ClassNameImport;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Namespace_;
use Argtyper202511\PhpParser\Node\Stmt\Use_;
use Argtyper202511\PhpParser\Node\UseItem;
use Argtyper202511\Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
final class AliasUsesResolver
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\UseImportsTraverser
     */
    private $useImportsTraverser;
    public function __construct(\Argtyper202511\Rector\CodingStyle\ClassNameImport\UseImportsTraverser $useImportsTraverser)
    {
        $this->useImportsTraverser = $useImportsTraverser;
    }
    /**
     * @param Stmt[] $stmts
     * @return string[]
     */
    public function resolveFromNode(Node $node, array $stmts): array
    {
        if (!$node instanceof Namespace_ && !$node instanceof FileWithoutNamespace) {
            /** @var Namespace_[]|FileWithoutNamespace[] $namespaces */
            $namespaces = array_filter($stmts, static function (Stmt $stmt): bool {
                return $stmt instanceof Namespace_ || $stmt instanceof FileWithoutNamespace;
            });
            if (count($namespaces) !== 1) {
                return [];
            }
            $node = current($namespaces);
        }
        return $this->resolveFromStmts($node->stmts);
    }
    /**
     * @param Stmt[] $stmts
     * @return string[]
     */
    public function resolveFromStmts(array $stmts): array
    {
        $aliasedUses = [];
        /** @param Use_::TYPE_* $useType */
        $this->useImportsTraverser->traverserStmts($stmts, static function (int $useType, UseItem $useItem, string $name) use (&$aliasedUses): void {
            if ($useType !== Use_::TYPE_NORMAL) {
                return;
            }
            if (!$useItem->alias instanceof Identifier) {
                return;
            }
            $aliasedUses[] = $name;
        });
        return $aliasedUses;
    }
}
