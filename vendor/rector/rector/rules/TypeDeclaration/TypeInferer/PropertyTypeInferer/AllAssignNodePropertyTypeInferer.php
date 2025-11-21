<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\ClassLike;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpParser\AstResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;
use Argtyper202511\Rector\ValueObject\Application\File;
final class AllAssignNodePropertyTypeInferer
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer
     */
    private $assignToPropertyTypeInferer;
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
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(AssignToPropertyTypeInferer $assignToPropertyTypeInferer, NodeNameResolver $nodeNameResolver, AstResolver $astResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->assignToPropertyTypeInferer = $assignToPropertyTypeInferer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function inferProperty(Property $property, ClassReflection $classReflection, File $file) : ?Type
    {
        if ($classReflection->getFileName() === $file->getFilePath()) {
            $className = $classReflection->getName();
            $classLike = $this->betterNodeFinder->findFirst($file->getNewStmts(), function (Node $node) use($className) : bool {
                return $node instanceof ClassLike && $this->nodeNameResolver->isName($node, $className);
            });
        } else {
            $classLike = $this->astResolver->resolveClassFromClassReflection($classReflection);
        }
        if (!$classLike instanceof ClassLike) {
            return null;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        return $this->assignToPropertyTypeInferer->inferPropertyInClassLike($property, $propertyName, $classLike);
    }
}
