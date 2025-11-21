<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\DeadCode\PhpDoc\DeadVarTagValueNodeAnalyzer;
use Argtyper202511\Rector\Doctrine\CodeQuality\Helper\SetterGetterFinder;
use Argtyper202511\Rector\Doctrine\NodeAnalyzer\DoctrineEntityDetector;
use Argtyper202511\Rector\Doctrine\NodeManipulator\ColumnPropertyTypeResolver;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Class_\TypeNullableEntityFromDocblockRector\TypeNullableEntityFromDocblockRectorTest
 */
final class TypeNullableEntityFromDocblockRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeManipulator\ColumnPropertyTypeResolver
     */
    private $columnPropertyTypeResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\DeadVarTagValueNodeAnalyzer
     */
    private $deadVarTagValueNodeAnalyzer;
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
    /**
     * @readonly
     * @var \Rector\Doctrine\CodeQuality\Helper\SetterGetterFinder
     */
    private $setterGetterFinder;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\DoctrineEntityDetector
     */
    private $doctrineEntityDetector;
    public function __construct(ColumnPropertyTypeResolver $columnPropertyTypeResolver, StaticTypeMapper $staticTypeMapper, DeadVarTagValueNodeAnalyzer $deadVarTagValueNodeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater, SetterGetterFinder $setterGetterFinder, DoctrineEntityDetector $doctrineEntityDetector)
    {
        $this->columnPropertyTypeResolver = $columnPropertyTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->deadVarTagValueNodeAnalyzer = $deadVarTagValueNodeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->setterGetterFinder = $setterGetterFinder;
        $this->doctrineEntityDetector = $doctrineEntityDetector;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add full nullable type coverage for Doctrine entity based on docblocks. Useful stepping stone to add type coverage while keeping entities safe to read and write getter/setters', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SomeEntity
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string")
     * @ORM\GeneratedValue
     */
    private $name;


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SomeEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @ORM\GeneratedValue
     */
    private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Class_
    {
        if (!$this->doctrineEntityDetector->detect($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            // property is already typed, skip
            if ($property->type instanceof Node) {
                continue;
            }
            $propertyType = $this->columnPropertyTypeResolver->resolve($property, \true);
            if (!$propertyType instanceof Type || $propertyType instanceof MixedType) {
                continue;
            }
            $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
            if (!$propertyTypeNode instanceof Node) {
                continue;
            }
            $property->type = $propertyTypeNode;
            $propertyItem = $property->props[0];
            // set default value to null if nullable
            if (!$propertyItem->default instanceof Expr) {
                $propertyItem->default = new ConstFetch(new Name('null'));
            }
            $hasChanged = \true;
            $this->removeVarTagIfNotUseful($property);
            $propertyName = $this->getName($property);
            $this->decorateGetterClassMethodReturnType($node, $propertyName, $propertyType);
            $this->decorateSetterClassMethodParameterType($node, $propertyName, $propertyType);
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function removeVarTagIfNotUseful(Property $property): void
    {
        // remove @var docblock if not useful
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        if (!$propertyPhpDocInfo instanceof PhpDocInfo) {
            return;
        }
        $propertyVarTagValueNode = $propertyPhpDocInfo->getVarTagValueNode();
        if (!$propertyVarTagValueNode instanceof VarTagValueNode) {
            return;
        }
        if (!$this->deadVarTagValueNodeAnalyzer->isDead($propertyVarTagValueNode, $property)) {
            return;
        }
        $propertyPhpDocInfo->removeByType(VarTagValueNode::class);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($property);
    }
    private function decorateGetterClassMethodReturnType(Class_ $class, string $propertyName, Type $propertyType): void
    {
        $getterClassMethod = $this->setterGetterFinder->findGetterClassMethod($class, $propertyName);
        if (!$getterClassMethod instanceof ClassMethod) {
            return;
        }
        // already known type
        if ($getterClassMethod->returnType instanceof Node) {
            return;
        }
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::RETURN);
        // no type to fill
        if (!$returnTypeNode instanceof Node) {
            return;
        }
        $getterClassMethod->returnType = $returnTypeNode;
        $this->removeReturnDocblock($getterClassMethod);
    }
    private function decorateSetterClassMethodParameterType(Class_ $class, string $propertyName, Type $propertyType): void
    {
        $setterClassMethod = $this->setterGetterFinder->findSetterClassMethod($class, $propertyName);
        if (!$setterClassMethod instanceof ClassMethod) {
            return;
        }
        if (count($setterClassMethod->params) !== 1) {
            return;
        }
        $soleParam = $setterClassMethod->params[0];
        // already known type
        if ($soleParam->type instanceof Node) {
            return;
        }
        $parameterTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PARAM);
        // no type to fill
        if (!$parameterTypeNode instanceof Node) {
            return;
        }
        $soleParam->type = $parameterTypeNode;
        $this->removeParamDocblock($setterClassMethod);
    }
    private function removeParamDocblock(ClassMethod $classMethod): void
    {
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$classMethodPhpDocInfo instanceof PhpDocInfo) {
            return;
        }
        if ($classMethodPhpDocInfo->getParamTagValueNodes() === []) {
            return;
        }
        $paramTagValueNode = $classMethodPhpDocInfo->getParamTagValueNodes()[0] ?? null;
        if ($paramTagValueNode instanceof ParamTagValueNode && $paramTagValueNode->description !== '') {
            return;
        }
        $classMethodPhpDocInfo->removeByType(ParamTagValueNode::class);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
    }
    private function removeReturnDocblock(ClassMethod $classMethod): void
    {
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$classMethodPhpDocInfo instanceof PhpDocInfo) {
            return;
        }
        if (!$classMethodPhpDocInfo->getReturnTagValue() instanceof ReturnTagValueNode) {
            return;
        }
        if ($classMethodPhpDocInfo->getReturnTagValue()->description !== '') {
            return;
        }
        $classMethodPhpDocInfo->removeByType(ReturnTagValueNode::class);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
    }
}
