<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Argtyper202511\Rector\PhpParser\AstResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Narrows return type from generic object to specific class in final classes/methods.
 *
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\NarrowObjectReturnTypeRector\NarrowObjectReturnTypeRectorTest
 */
final class NarrowObjectReturnTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    public function __construct(BetterNodeFinder $betterNodeFinder, ReflectionResolver $reflectionResolver, AstResolver $astResolver, StaticTypeMapper $staticTypeMapper, TypeComparator $typeComparator, PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->reflectionResolver = $reflectionResolver;
        $this->astResolver = $astResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeComparator = $typeComparator;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Narrows return type from generic object to specific class in final classes/methods', [new CodeSample(<<<'CODE_SAMPLE'
final class TalkFactory extends AbstractFactory
{
    protected function build(): object
    {
        return new ConferenceTalk();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class TalkFactory extends AbstractFactory
{
    protected function build(): ConferenceTalk
    {
        return new ConferenceTalk();
    }
}
CODE_SAMPLE
), new CodeSample(<<<'CODE_SAMPLE'
final class TalkFactory
{
    public function createConferenceTalk(): Talk
    {
        return new ConferenceTalk();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class TalkFactory
{
    public function createConferenceTalk(): ConferenceTalk
    {
        return new ConferenceTalk();
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
        $returnType = $node->returnType;
        if (!$returnType instanceof Identifier && !$returnType instanceof FullyQualified) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if (!$classReflection->isFinalByKeyword() && !$node->isFinal()) {
            return null;
        }
        if ($this->hasParentMethodWithNonObjectReturn($node)) {
            return null;
        }
        $actualReturnClass = $this->getActualReturnClass($node);
        if ($actualReturnClass === null) {
            return null;
        }
        $declaredType = $returnType->toString();
        if ($declaredType === $actualReturnClass) {
            return null;
        }
        if ($this->isDeclaredTypeFinal($declaredType)) {
            return null;
        }
        if ($this->isActualTypeAnonymous($actualReturnClass)) {
            return null;
        }
        if (!$this->isNarrowingValid($declaredType, $actualReturnClass)) {
            return null;
        }
        $node->returnType = new FullyQualified($actualReturnClass);
        $this->updateDocblock($node, $actualReturnClass);
        return $node;
    }
    private function updateDocblock(ClassMethod $classMethod, string $actualReturnClass): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return;
        }
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return;
        }
        if ($returnTagValueNode->type instanceof IdentifierTypeNode) {
            $oldType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($returnTagValueNode->type, $classMethod);
        } elseif ($returnTagValueNode->type instanceof GenericTypeNode) {
            $oldType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($returnTagValueNode->type->type, $classMethod);
        } else {
            return;
        }
        if ($oldType instanceof ObjectType) {
            $objectType = new ObjectType($actualReturnClass);
            if ($this->typeComparator->areTypesEqual($oldType, $objectType)) {
                return;
            }
        }
        if ($returnTagValueNode->type instanceof IdentifierTypeNode) {
            $returnTagValueNode->type = new FullyQualifiedIdentifierTypeNode($actualReturnClass);
        } else {
            $returnTagValueNode->type->type = new FullyQualifiedIdentifierTypeNode($actualReturnClass);
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
    }
    private function isDeclaredTypeFinal(string $declaredType): bool
    {
        if ($declaredType === 'object') {
            return \false;
        }
        $declaredObjectType = new ObjectType($declaredType);
        $classReflection = $declaredObjectType->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->isFinalByKeyword();
    }
    private function isActualTypeAnonymous(string $actualType): bool
    {
        $actualObjectType = new ObjectType($actualType);
        $classReflection = $actualObjectType->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->isAnonymous();
    }
    private function isNarrowingValid(string $declaredType, string $actualType): bool
    {
        if ($declaredType === 'object') {
            return \true;
        }
        $actualObjectType = new ObjectType($actualType);
        $declaredObjectType = new ObjectType($declaredType);
        return $declaredObjectType->isSuperTypeOf($actualObjectType)->yes();
    }
    private function hasParentMethodWithNonObjectReturn(ClassMethod $classMethod): bool
    {
        if ($classMethod->isPrivate()) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        $ancestors = array_filter($classReflection->getAncestors(), function (ClassReflection $ancestorClassReflection) use ($classReflection): bool {
            return $classReflection->getName() !== $ancestorClassReflection->getName();
        });
        $methodName = $this->getName($classMethod);
        foreach ($ancestors as $ancestor) {
            if ($ancestor->getFileName() === null) {
                continue;
            }
            if (!$ancestor->hasNativeMethod($methodName)) {
                continue;
            }
            $parentClassMethod = $this->astResolver->resolveClassMethod($ancestor->getName(), $methodName);
            if (!$parentClassMethod instanceof ClassMethod) {
                continue;
            }
            $parentReturnType = $parentClassMethod->returnType;
            if (!$parentReturnType instanceof Node) {
                continue;
            }
            if ($parentReturnType instanceof Identifier && $parentReturnType->name === 'object') {
                continue;
            }
            return \true;
        }
        return \false;
    }
    private function getActualReturnClass(ClassMethod $classMethod): ?string
    {
        $returnStatements = $this->betterNodeFinder->findReturnsScoped($classMethod);
        if ($returnStatements === []) {
            return null;
        }
        $returnedClass = null;
        foreach ($returnStatements as $returnStatement) {
            if ($returnStatement->expr === null) {
                return null;
            }
            $returnType = $this->nodeTypeResolver->getNativeType($returnStatement->expr);
            if (!$returnType->isObject()->yes()) {
                return null;
            }
            $classNames = $returnType->getObjectClassNames();
            if (count($classNames) !== 1) {
                return null;
            }
            $className = $classNames[0];
            if ($returnedClass === null) {
                $returnedClass = $className;
            } elseif ($returnedClass !== $className) {
                return null;
            }
        }
        return $returnedClass;
    }
}
