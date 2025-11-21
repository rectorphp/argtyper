<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp74\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\ComplexType;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\NullableType;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\UnionType;
use Argtyper202511\PHPStan\Reflection\ClassMemberAccessAnswerer;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Reflection\Php\PhpMethodReflection;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\StaticType;
use Argtyper202511\PHPStan\Type\ThisType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\DeadCode\PhpDoc\TagRemover\ReturnTagRemover;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
use Argtyper202511\Rector\Util\Reflection\PrivatesAccessor;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/migration74.new-features.php#migration74.new-features.core.type-variance
 *
 * @see \Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\DowngradeCovariantReturnTypeRectorTest
 */
final class DowngradeCovariantReturnTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\ReturnTagRemover
     */
    private $returnTagRemover;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\Util\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(PhpDocTypeChanger $phpDocTypeChanger, ReturnTagRemover $returnTagRemover, ReflectionResolver $reflectionResolver, PrivatesAccessor $privatesAccessor, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->returnTagRemover = $returnTagRemover;
        $this->reflectionResolver = $reflectionResolver;
        $this->privatesAccessor = $privatesAccessor;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Make method return same type as parent', [new CodeSample(<<<'CODE_SAMPLE'
class ParentType {}
class ChildType extends ParentType {}

class A
{
    public function covariantReturnTypes(): ParentType
    {
    }
}

class B extends A
{
    public function covariantReturnTypes(): ChildType
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class ParentType {}
class ChildType extends ParentType {}

class A
{
    public function covariantReturnTypes(): ParentType
    {
    }
}

class B extends A
{
    /**
     * @return ChildType
     */
    public function covariantReturnTypes(): ParentType
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->returnType === null) {
            return null;
        }
        $parentReturnType = $this->resolveDifferentAncestorReturnType($node, $node->returnType);
        if ($parentReturnType instanceof MixedType) {
            return null;
        }
        // The return type name could either be a classname, without the leading "\",
        // or one among the reserved identifiers ("static", "self", "iterable", etc)
        // To find out which is the case, check if this name exists as a class
        $parentReturnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parentReturnType, TypeKind::RETURN);
        if (!$parentReturnTypeNode instanceof Node) {
            return null;
        }
        // Make it nullable?
        if ($node->returnType instanceof NullableType && !$parentReturnTypeNode instanceof ComplexType) {
            $parentReturnTypeNode = new NullableType($parentReturnTypeNode);
        }
        // skip if type is already set
        if ($this->nodeComparator->areNodesEqual($parentReturnTypeNode, $node->returnType)) {
            return null;
        }
        if ($parentReturnType instanceof ThisType) {
            return null;
        }
        // Add the docblock before changing the type
        $this->addDocBlockReturn($node);
        $node->returnType = $parentReturnTypeNode;
        return $node;
    }
    /**
     * @param \PhpParser\Node\UnionType|\PhpParser\Node\NullableType|\PhpParser\Node\Name|\PhpParser\Node\Identifier|\PhpParser\Node\ComplexType $returnTypeNode
     */
    private function resolveDifferentAncestorReturnType(ClassMethod $classMethod, $returnTypeNode): Type
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return new MixedType();
        }
        if ($returnTypeNode instanceof UnionType) {
            return new MixedType();
        }
        $bareReturnType = $returnTypeNode instanceof NullableType ? $returnTypeNode->type : $returnTypeNode;
        $returnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($bareReturnType);
        $methodName = $this->getName($classMethod);
        /** @var ClassReflection[] $parentClassesAndInterfaces */
        $parentClassesAndInterfaces = array_merge($classReflection->getParents(), $classReflection->getInterfaces());
        return $this->resolveMatchingReturnType($parentClassesAndInterfaces, $methodName, $classMethod, $returnType);
    }
    private function addDocBlockReturn(ClassMethod $classMethod): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        // keep return type if already set one
        if (!$phpDocInfo->getReturnType() instanceof MixedType) {
            return;
        }
        /** @var Node $returnType */
        $returnType = $classMethod->returnType;
        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($returnType);
        $this->phpDocTypeChanger->changeReturnType($classMethod, $phpDocInfo, $type);
        $this->returnTagRemover->removeReturnTagIfUseless($phpDocInfo, $classMethod);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
    }
    /**
     * @param ClassReflection[] $parentClassesAndInterfaces
     */
    private function resolveMatchingReturnType(array $parentClassesAndInterfaces, string $methodName, ClassMethod $classMethod, Type $returnType): Type
    {
        foreach ($parentClassesAndInterfaces as $parentClassAndInterface) {
            $parentClassAndInterfaceHasMethod = $parentClassAndInterface->hasMethod($methodName);
            if (!$parentClassAndInterfaceHasMethod) {
                continue;
            }
            $classMethodScope = $classMethod->getAttribute(AttributeKey::SCOPE);
            /** @var ClassMemberAccessAnswerer $classMethodScope */
            $parameterMethodReflection = $parentClassAndInterface->getMethod($methodName, $classMethodScope);
            if (!$parameterMethodReflection instanceof PhpMethodReflection) {
                continue;
            }
            /** @var Type $parentReturnType */
            $parentReturnType = $this->privatesAccessor->callPrivateMethod($parameterMethodReflection, 'getNativeReturnType', []);
            // skip "parent" reference if correct
            if ($returnType instanceof ParentStaticType && $parentReturnType->accepts($returnType, \true)->yes()) {
                continue;
            }
            if ($parentReturnType instanceof StaticType && $returnType->accepts($parentReturnType, \true)->yes()) {
                continue;
            }
            if ($parentReturnType->equals($returnType)) {
                continue;
            }
            if ($this->isNullable($parentReturnType, $returnType)) {
                continue;
            }
            // This is an ancestor class with a different return type
            return $parentReturnType;
        }
        return new MixedType();
    }
    private function isNullable(Type $parentReturnType, Type $returnType): bool
    {
        if (!$parentReturnType instanceof \Argtyper202511\PHPStan\Type\UnionType) {
            return \false;
        }
        if (!TypeCombinator::containsNull($parentReturnType)) {
            return \false;
        }
        foreach ($parentReturnType->getTypes() as $type) {
            if ($type->equals($returnType)) {
                return \true;
            }
        }
        return \false;
    }
}
