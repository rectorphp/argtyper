<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Trait_;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\TraitTypeResolver\TraitTypeResolverTest
 *
 * @implements NodeTypeResolverInterface<Trait_>
 */
final class TraitTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses(): array
    {
        return [Trait_::class];
    }
    /**
     * @param Trait_ $node
     */
    public function resolve(Node $node): Type
    {
        $traitName = (string) $node->namespacedName;
        if (!$this->reflectionProvider->hasClass($traitName)) {
            return new MixedType();
        }
        $classReflection = $this->reflectionProvider->getClass($traitName);
        $types = [];
        $types[] = new ObjectType($traitName);
        foreach ($classReflection->getTraits() as $usedTraitReflection) {
            $types[] = new ObjectType($usedTraitReflection->getName());
        }
        if (count($types) === 1) {
            return $types[0];
        }
        return new UnionType($types);
    }
}
