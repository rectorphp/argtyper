<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\FamilyTree\Reflection;

use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Interface_;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
final class FamilyRelationsAnalyzer
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(ReflectionProvider $reflectionProvider, NodeNameResolver $nodeNameResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @api
     * @return string[]
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Name $classOrName
     */
    public function getClassLikeAncestorNames($classOrName) : array
    {
        $ancestorNames = [];
        if ($classOrName instanceof Name) {
            $fullName = $this->nodeNameResolver->getName($classOrName);
            if (!$this->reflectionProvider->hasClass($fullName)) {
                return [];
            }
            $classReflection = $this->reflectionProvider->getClass($fullName);
            $ancestors = \array_merge($classReflection->getParents(), $classReflection->getInterfaces());
            return \array_map(static function (ClassReflection $classReflection) : string {
                return $classReflection->getName();
            }, $ancestors);
        }
        if ($classOrName instanceof Interface_) {
            foreach ($classOrName->extends as $extendInterfaceName) {
                $ancestorNames[] = $this->nodeNameResolver->getName($extendInterfaceName);
                $ancestorNames = \array_merge($ancestorNames, $this->getClassLikeAncestorNames($extendInterfaceName));
            }
        }
        if ($classOrName instanceof Class_) {
            if ($classOrName->extends instanceof Name) {
                $ancestorNames[] = $this->nodeNameResolver->getName($classOrName->extends);
                $ancestorNames = \array_merge($ancestorNames, $this->getClassLikeAncestorNames($classOrName->extends));
            }
            foreach ($classOrName->implements as $implement) {
                $ancestorNames[] = $this->nodeNameResolver->getName($implement);
                $ancestorNames = \array_merge($ancestorNames, $this->getClassLikeAncestorNames($implement));
            }
        }
        /** @var string[] $ancestorNames */
        return $ancestorNames;
    }
}
