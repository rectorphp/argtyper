<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\PropertyRenamer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticPropertyFetch;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt\ClassLike;
use Argtyper202511\PhpParser\Node\VarLikeIdentifier;
use Argtyper202511\Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Argtyper202511\Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class PropertyFetchRenamer
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    public function renamePropertyFetchesInClass(ClassLike $classLike, string $currentName, string $expectedName) : void
    {
        // 1. replace property fetch rename in whole class
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classLike, function (Node $node) use($currentName, $expectedName) : ?Node {
            if (!$this->propertyFetchAnalyzer->isLocalPropertyFetchName($node, $currentName)) {
                return null;
            }
            /** @var StaticPropertyFetch|PropertyFetch $node */
            $node->name = $node instanceof PropertyFetch ? new Identifier($expectedName) : new VarLikeIdentifier($expectedName);
            return $node;
        });
    }
}
