<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\NullsafePropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticPropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
final class PropertyWriteonlyAnalyzer
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function hasClassDynamicPropertyNames(Class_ $class) : bool
    {
        $isImplementsJsonSerializable = $this->nodeTypeResolver->isObjectType($class, new ObjectType('JsonSerializable'));
        return (bool) $this->betterNodeFinder->findFirst($class, function (Node $node) use($isImplementsJsonSerializable) : bool {
            if ($isImplementsJsonSerializable && $node instanceof FuncCall && $this->nodeNameResolver->isName($node, 'get_object_vars') && !$node->isFirstClassCallable()) {
                $firstArg = $node->getArgs()[0] ?? null;
                if ($firstArg instanceof Arg && $firstArg->value instanceof Variable && $firstArg->value->name === 'this') {
                    return \true;
                }
            }
            if (!$node instanceof PropertyFetch && !$node instanceof NullsafePropertyFetch) {
                return \false;
            }
            // has dynamic name - could be anything
            return $node->name instanceof Expr;
        });
    }
    /**
     * The property fetches are always only assigned to, nothing else
     *
     * @param array<PropertyFetch|StaticPropertyFetch|NullsafePropertyFetch> $propertyFetches
     */
    public function arePropertyFetchesExclusivelyBeingAssignedTo(array $propertyFetches) : bool
    {
        foreach ($propertyFetches as $propertyFetch) {
            if ((bool) $propertyFetch->getAttribute(AttributeKey::IS_MULTI_ASSIGN, \false)) {
                return \false;
            }
            if ((bool) $propertyFetch->getAttribute(AttributeKey::IS_ASSIGNED_TO, \false)) {
                return \false;
            }
            if ((bool) $propertyFetch->getAttribute(AttributeKey::IS_BEING_ASSIGNED, \false)) {
                continue;
            }
            return \false;
        }
        return \true;
    }
}
