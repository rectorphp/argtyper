<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\NodeFactory;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Type\FloatType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
final class JMSTypePropertyTypeFactory
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper
     */
    private $scalarStringToTypeMapper;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover
     */
    private $varTagRemover;
    public function __construct(ScalarStringToTypeMapper $scalarStringToTypeMapper, StaticTypeMapper $staticTypeMapper, PhpDocInfoFactory $phpDocInfoFactory, VarTagRemover $varTagRemover)
    {
        $this->scalarStringToTypeMapper = $scalarStringToTypeMapper;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->varTagRemover = $varTagRemover;
    }
    public function createObjectTypeNode(string $typeValue) : ?Node
    {
        // skip generic iterable types
        if (\strpos($typeValue, '<') !== \false) {
            return null;
        }
        $type = $this->scalarStringToTypeMapper->mapScalarStringToType($typeValue);
        if ($type instanceof MixedType) {
            // fallback to object type
            $type = new ObjectType($typeValue);
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PROPERTY);
    }
    public function createScalarTypeNode(string $typeValue, Property $property) : ?Node
    {
        if ($typeValue === 'float') {
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            // fallback to string, as most likely string representation of float
            if ($propertyPhpDocInfo instanceof PhpDocInfo && $propertyPhpDocInfo->getVarType() instanceof StringType) {
                $this->varTagRemover->removeVarTag($property);
                return new Identifier('string');
            }
        }
        if ($typeValue === 'string') {
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            // fallback to string, as most likely string representation of float
            if ($propertyPhpDocInfo instanceof PhpDocInfo && $propertyPhpDocInfo->getVarType() instanceof FloatType) {
                $this->varTagRemover->removeVarTag($property);
                return new Identifier('float');
            }
        }
        $type = $this->scalarStringToTypeMapper->mapScalarStringToType($typeValue);
        if ($type instanceof MixedType) {
            return null;
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PROPERTY);
    }
}
