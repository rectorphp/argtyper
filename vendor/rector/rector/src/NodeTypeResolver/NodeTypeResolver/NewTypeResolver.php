<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\ObjectWithoutClassType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\Enum\ObjectReference;
use Argtyper202511\Rector\NodeAnalyzer\ClassAnalyzer;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\ObjectWithoutClassTypeWithParentTypes;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
/**
 * @implements NodeTypeResolverInterface<New_>
 */
final class NewTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, ClassAnalyzer $classAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classAnalyzer = $classAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses(): array
    {
        return [New_::class];
    }
    /**
     * @param New_ $node
     */
    public function resolve(Node $node): Type
    {
        if ($node->class instanceof Name) {
            $className = $this->nodeNameResolver->getName($node->class);
            if (!in_array($className, [ObjectReference::SELF, ObjectReference::PARENT], \true)) {
                return new ObjectType($className);
            }
        }
        $isAnonymousClass = $this->classAnalyzer->isAnonymousClass($node->class);
        if ($isAnonymousClass) {
            return $this->resolveAnonymousClassType($node);
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            // new node probably
            return new MixedType();
        }
        return $scope->getType($node);
    }
    private function resolveAnonymousClassType(New_ $new): ObjectWithoutClassType
    {
        if (!$new->class instanceof Class_) {
            return new ObjectWithoutClassType();
        }
        $directParentTypes = [];
        /** @var Class_ $class */
        $class = $new->class;
        if ($class->extends instanceof Name) {
            $parentClass = (string) $class->extends;
            $directParentTypes[] = new FullyQualifiedObjectType($parentClass);
        }
        foreach ($class->implements as $implement) {
            $parentClass = (string) $implement;
            $directParentTypes[] = new FullyQualifiedObjectType($parentClass);
        }
        if ($directParentTypes !== []) {
            return new ObjectWithoutClassTypeWithParentTypes($directParentTypes);
        }
        return new ObjectWithoutClassType();
    }
}
