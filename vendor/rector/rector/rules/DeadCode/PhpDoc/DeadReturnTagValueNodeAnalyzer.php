<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\PhpDoc;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Argtyper202511\Rector\DeadCode\PhpDoc\Guard\StandaloneTypeRemovalGuard;
use Argtyper202511\Rector\DeadCode\PhpDoc\Guard\TemplateTypeRemovalGuard;
use Argtyper202511\Rector\DeadCode\TypeNodeAnalyzer\GenericTypeNodeAnalyzer;
use Argtyper202511\Rector\DeadCode\TypeNodeAnalyzer\MixedArrayTypeNodeAnalyzer;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
final class DeadReturnTagValueNodeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\DeadCode\TypeNodeAnalyzer\GenericTypeNodeAnalyzer
     */
    private $genericTypeNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\DeadCode\TypeNodeAnalyzer\MixedArrayTypeNodeAnalyzer
     */
    private $mixedArrayTypeNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\Guard\StandaloneTypeRemovalGuard
     */
    private $standaloneTypeRemovalGuard;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\Guard\TemplateTypeRemovalGuard
     */
    private $templateTypeRemovalGuard;
    public function __construct(TypeComparator $typeComparator, GenericTypeNodeAnalyzer $genericTypeNodeAnalyzer, MixedArrayTypeNodeAnalyzer $mixedArrayTypeNodeAnalyzer, StandaloneTypeRemovalGuard $standaloneTypeRemovalGuard, PhpDocTypeChanger $phpDocTypeChanger, StaticTypeMapper $staticTypeMapper, TemplateTypeRemovalGuard $templateTypeRemovalGuard)
    {
        $this->typeComparator = $typeComparator;
        $this->genericTypeNodeAnalyzer = $genericTypeNodeAnalyzer;
        $this->mixedArrayTypeNodeAnalyzer = $mixedArrayTypeNodeAnalyzer;
        $this->standaloneTypeRemovalGuard = $standaloneTypeRemovalGuard;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->templateTypeRemovalGuard = $templateTypeRemovalGuard;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function isDead(ReturnTagValueNode $returnTagValueNode, $functionLike) : bool
    {
        $returnType = $functionLike->getReturnType();
        if ($returnType === null) {
            return \false;
        }
        if ($returnTagValueNode->description !== '') {
            return \false;
        }
        if ($returnTagValueNode->type instanceof GenericTypeNode) {
            return \false;
        }
        $docType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($returnTagValueNode->type, $functionLike);
        if (!$this->templateTypeRemovalGuard->isLegal($docType)) {
            return \false;
        }
        $scope = $functionLike->getAttribute(AttributeKey::SCOPE);
        if ($scope instanceof Scope && $scope->isInTrait() && $returnTagValueNode->type instanceof ThisTypeNode) {
            return \false;
        }
        if (!$this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual($returnType, $returnTagValueNode->type, $functionLike)) {
            return $this->isDeadNotEqual($returnTagValueNode, $returnType, $functionLike);
        }
        if ($this->phpDocTypeChanger->isAllowed($returnTagValueNode->type)) {
            return \false;
        }
        if (!$returnTagValueNode->type instanceof BracketsAwareUnionTypeNode) {
            return $this->standaloneTypeRemovalGuard->isLegal($returnTagValueNode->type, $returnType);
        }
        if ($this->genericTypeNodeAnalyzer->hasGenericType($returnTagValueNode->type)) {
            return \false;
        }
        if ($this->mixedArrayTypeNodeAnalyzer->hasMixedArrayType($returnTagValueNode->type)) {
            return \false;
        }
        return !$this->hasTrueFalsePseudoType($returnTagValueNode->type);
    }
    private function isVoidReturnType(Node $node) : bool
    {
        return $node instanceof Identifier && $node->toString() === 'void';
    }
    private function isNeverReturnType(Node $node) : bool
    {
        return $node instanceof Identifier && $node->toString() === 'never';
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function isDeadNotEqual(ReturnTagValueNode $returnTagValueNode, Node $node, $functionLike) : bool
    {
        if ($returnTagValueNode->type instanceof IdentifierTypeNode && (string) $returnTagValueNode->type === 'void') {
            return \true;
        }
        if (!$this->hasUsefulPhpdocType($returnTagValueNode, $node)) {
            return \true;
        }
        $nodeType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node);
        $docType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($returnTagValueNode->type, $functionLike);
        return $docType instanceof UnionType && $this->typeComparator->areTypesEqual(TypeCombinator::removeNull($docType), $nodeType);
    }
    private function hasTrueFalsePseudoType(BracketsAwareUnionTypeNode $bracketsAwareUnionTypeNode) : bool
    {
        $unionTypes = $bracketsAwareUnionTypeNode->types;
        foreach ($unionTypes as $unionType) {
            if (!$unionType instanceof IdentifierTypeNode) {
                continue;
            }
            $name = \strtolower((string) $unionType);
            if (\in_array($name, ['true', 'false'], \true)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * exact different between @return and node return type
     * @param mixed $returnType
     */
    private function hasUsefulPhpdocType(ReturnTagValueNode $returnTagValueNode, $returnType) : bool
    {
        if ($returnTagValueNode->type instanceof IdentifierTypeNode && $returnTagValueNode->type->name === 'mixed') {
            return \false;
        }
        if (!$this->isVoidReturnType($returnType)) {
            return !$this->isNeverReturnType($returnType);
        }
        if (!$returnTagValueNode->type instanceof IdentifierTypeNode || (string) $returnTagValueNode->type !== 'never') {
            return \false;
        }
        return !$this->isNeverReturnType($returnType);
    }
}
