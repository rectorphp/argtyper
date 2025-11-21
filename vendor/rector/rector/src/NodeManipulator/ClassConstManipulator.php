<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeManipulator;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt\ClassConst;
use Argtyper202511\PhpParser\Node\Stmt\ClassLike;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\PhpParser\AstResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
final class ClassConstManipulator
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, AstResolver $astResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function hasClassConstFetch(ClassConst $classConst, ClassReflection $classReflection): bool
    {
        if (!$classReflection->isClass() && !$classReflection->isEnum()) {
            return \true;
        }
        $className = $classReflection->getName();
        $objectType = new ObjectType($className);
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            $ancestorClass = $this->astResolver->resolveClassFromClassReflection($ancestorClassReflection);
            if (!$ancestorClass instanceof ClassLike) {
                continue;
            }
            // has in class?
            $isClassConstFetchFound = (bool) $this->betterNodeFinder->findFirst($ancestorClass, function (Node $node) use ($classConst, $className, $objectType): bool {
                // property + static fetch
                if (!$node instanceof ClassConstFetch) {
                    return \false;
                }
                return $this->isNameMatch($node, $classConst, $className, $objectType);
            });
            if ($isClassConstFetchFound) {
                return \true;
            }
        }
        return \false;
    }
    private function isNameMatch(ClassConstFetch $classConstFetch, ClassConst $classConst, string $className, ObjectType $objectType): bool
    {
        $classConstName = (string) $this->nodeNameResolver->getName($classConst);
        $selfConstantName = 'self::' . $classConstName;
        $staticConstantName = 'static::' . $classConstName;
        $classNameConstantName = $className . '::' . $classConstName;
        if ($this->nodeNameResolver->isNames($classConstFetch, [$selfConstantName, $staticConstantName, $classNameConstantName])) {
            return \true;
        }
        if ($this->nodeTypeResolver->isObjectType($classConstFetch->class, $objectType)) {
            if (!$classConstFetch->name instanceof Identifier) {
                return \true;
            }
            return $this->nodeNameResolver->isName($classConstFetch->name, $classConstName);
        }
        return \false;
    }
}
