<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclarationDocblocks;

use Argtyper202511\PhpParser\Node\FunctionLike;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\NeverType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Argtyper202511\Rector\Privatization\TypeManipulator\TypeNormalizer;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
final class NodeDocblockTypeDecorator
{
    /**
     * @readonly
     * @var \Rector\Privatization\TypeManipulator\TypeNormalizer
     */
    private $typeNormalizer;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    public function __construct(TypeNormalizer $typeNormalizer, StaticTypeMapper $staticTypeMapper, PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->typeNormalizer = $typeNormalizer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function decorateGenericIterableParamType(Type $type, PhpDocInfo $phpDocInfo, FunctionLike $functionLike, Param $param, string $parameterName): bool
    {
        if ($this->isBareMixedType($type)) {
            // no value
            return \false;
        }
        $typeNode = $this->createTypeNode($type);
        // no value iterable type
        if ($typeNode instanceof IdentifierTypeNode) {
            return \false;
        }
        $this->phpDocTypeChanger->changeParamTypeNode($functionLike, $phpDocInfo, $param, $parameterName, $typeNode);
        return \true;
    }
    /**
     * @param \PHPStan\Type\Type|\PHPStan\PhpDocParser\Ast\Type\TypeNode $typeOrTypeNode
     */
    public function decorateGenericIterableReturnType($typeOrTypeNode, PhpDocInfo $classMethodPhpDocInfo, FunctionLike $functionLike): bool
    {
        if ($typeOrTypeNode instanceof TypeNode) {
            $type = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($typeOrTypeNode, $functionLike);
        } else {
            $type = $typeOrTypeNode;
        }
        if ($this->isBareMixedType($type)) {
            // no value
            return \false;
        }
        if ($typeOrTypeNode instanceof TypeNode) {
            $typeNode = $typeOrTypeNode;
        } else {
            $typeNode = $this->createTypeNode($typeOrTypeNode);
        }
        // no value iterable type
        if ($typeNode instanceof IdentifierTypeNode) {
            return \false;
        }
        $this->phpDocTypeChanger->changeReturnTypeNode($functionLike, $classMethodPhpDocInfo, $typeNode);
        return \true;
    }
    public function decorateGenericIterableVarType(Type $type, PhpDocInfo $phpDocInfo, Property $property): bool
    {
        $typeNode = $this->createTypeNode($type);
        if ($this->isBareMixedType($type)) {
            // no value
            return \false;
        }
        // no value iterable type
        if ($typeNode instanceof IdentifierTypeNode) {
            return \false;
        }
        $this->phpDocTypeChanger->changeVarTypeNode($property, $phpDocInfo, $typeNode);
        return \true;
    }
    private function createTypeNode(Type $type): TypeNode
    {
        $generalizedType = $this->typeNormalizer->generalizeConstantTypes($type);
        // turn into rather generic short return typeOrTypeNode, to keep it open to extension later and readable to human
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($generalizedType);
        if ($typeNode instanceof IdentifierTypeNode && $typeNode->name === 'mixed') {
            return new ArrayTypeNode($typeNode);
        }
        return $typeNode;
    }
    private function isBareMixedType(Type $type): bool
    {
        if ($type instanceof MixedType) {
            return \true;
        }
        $normalizedResolvedParameterType = $this->typeNormalizer->generalizeConstantTypes($type);
        // most likely mixed, skip
        return $this->isArrayMixed($normalizedResolvedParameterType);
    }
    private function isArrayMixed(Type $type): bool
    {
        if (!$type instanceof ArrayType) {
            return \false;
        }
        if ($type->getItemType() instanceof NeverType) {
            return \true;
        }
        if (!$type->getItemType() instanceof MixedType) {
            return \false;
        }
        return $type->getKeyType() instanceof IntegerType;
    }
}
