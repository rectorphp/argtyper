<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\ClassNameImport;

use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Use_;
use Argtyper202511\PhpParser\Node\UseItem;
use Argtyper202511\Rector\CodingStyle\ClassNameImport\ValueObject\UsedImports;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class UsedImportsResolver
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\UseImportsTraverser
     */
    private $useImportsTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, \Argtyper202511\Rector\CodingStyle\ClassNameImport\UseImportsTraverser $useImportsTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->useImportsTraverser = $useImportsTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param Stmt[] $stmts
     */
    public function resolveForStmts(array $stmts): UsedImports
    {
        $usedImports = [];
        /** @var Class_|null $class */
        $class = $this->betterNodeFinder->findFirstInstanceOf($stmts, Class_::class);
        // add class itself
        // is not anonymous class
        if ($class instanceof Class_) {
            $className = (string) $this->nodeNameResolver->getName($class);
            $usedImports[] = new FullyQualifiedObjectType($className);
        }
        $usedConstImports = [];
        $usedFunctionImports = [];
        /** @param Use_::TYPE_* $useType */
        $this->useImportsTraverser->traverserStmts($stmts, static function (int $useType, UseItem $useItem, string $name) use (&$usedImports, &$usedFunctionImports, &$usedConstImports): void {
            if ($useType === Use_::TYPE_NORMAL) {
                if ($useItem->alias instanceof Identifier) {
                    $usedImports[] = new AliasedObjectType($useItem->alias->toString(), $name);
                } else {
                    $usedImports[] = new FullyQualifiedObjectType($name);
                }
            }
            if ($useType === Use_::TYPE_FUNCTION) {
                $usedFunctionImports[] = new FullyQualifiedObjectType($name);
            }
            if ($useType === Use_::TYPE_CONSTANT) {
                $usedConstImports[] = new FullyQualifiedObjectType($name);
            }
        });
        return new UsedImports($usedImports, $usedFunctionImports, $usedConstImports);
    }
}
