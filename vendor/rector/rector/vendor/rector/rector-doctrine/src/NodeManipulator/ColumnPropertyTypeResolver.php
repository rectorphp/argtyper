<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\NodeManipulator;

use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\FloatType;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\NullType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Doctrine\Enum\MappingClass;
use Argtyper202511\Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
final class ColumnPropertyTypeResolver
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttributeFinder
     */
    private $attributeFinder;
    /**
     * @var array<string, Type>
     * @readonly
     */
    private $doctrineTypeToScalarType;
    /**
     * @var string
     */
    private const DATE_TIME_INTERFACE = 'DateTimeInterface';
    /**
     * @param array<string, Type> $doctrineTypeToScalarType
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/basic-mapping.html#doctrine-mapping-types
     */
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, TypeFactory $typeFactory, AttributeFinder $attributeFinder, ?array $doctrineTypeToScalarType = null)
    {
        $doctrineTypeToScalarType = $doctrineTypeToScalarType ?? [
            'tinyint' => new BooleanType(),
            'boolean' => new BooleanType(),
            // integers
            'smallint' => new IntegerType(),
            'mediumint' => new IntegerType(),
            'int' => new IntegerType(),
            'integer' => new IntegerType(),
            'numeric' => new IntegerType(),
            // floats
            'float' => new FloatType(),
            'double' => new FloatType(),
            'real' => new FloatType(),
            // strings
            'decimal' => new StringType(),
            'bigint' => new StringType(),
            'tinytext' => new StringType(),
            'mediumtext' => new StringType(),
            'longtext' => new StringType(),
            'text' => new StringType(),
            'varchar' => new StringType(),
            'string' => new StringType(),
            'char' => new StringType(),
            'longblob' => new MixedType(),
            'blob' => new MixedType(),
            'mediumblob' => new MixedType(),
            'tinyblob' => new MixedType(),
            'binary' => new StringType(),
            'varbinary' => new StringType(),
            'set' => new StringType(),
            // date time objects
            'date' => new ObjectType(self::DATE_TIME_INTERFACE),
            'datetime' => new ObjectType(self::DATE_TIME_INTERFACE),
            'timestamp' => new ObjectType(self::DATE_TIME_INTERFACE),
            'time' => new ObjectType(self::DATE_TIME_INTERFACE),
            'year' => new ObjectType(self::DATE_TIME_INTERFACE),
        ];
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->typeFactory = $typeFactory;
        $this->attributeFinder = $attributeFinder;
        $this->doctrineTypeToScalarType = $doctrineTypeToScalarType;
    }
    public function resolve(Property $property, bool $isNullable) : ?Type
    {
        $expr = $this->attributeFinder->findAttributeByClassArgByName($property, MappingClass::COLUMN, 'type');
        if ($expr instanceof String_) {
            return $this->createPHPStanTypeFromDoctrineStringType($expr->value, $isNullable);
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        return $this->resolveFromPhpDocInfo($phpDocInfo, $isNullable);
    }
    private function resolveFromPhpDocInfo(PhpDocInfo $phpDocInfo, bool $isNullable) : ?\Argtyper202511\PHPStan\Type\Type
    {
        $doctrineAnnotationTagValueNode = $phpDocInfo->findOneByAnnotationClass(MappingClass::COLUMN);
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        $typeArrayItemNode = $doctrineAnnotationTagValueNode->getValue('type');
        if (!$typeArrayItemNode instanceof ArrayItemNode) {
            return new MixedType();
        }
        $typeValue = $typeArrayItemNode->value;
        if ($typeValue instanceof StringNode) {
            $typeValue = $typeValue->value;
        }
        if (!\is_string($typeValue)) {
            return null;
        }
        return $this->createPHPStanTypeFromDoctrineStringType($typeValue, $isNullable);
    }
    private function createPHPStanTypeFromDoctrineStringType(string $type, bool $isNullable) : Type
    {
        $scalarType = $this->doctrineTypeToScalarType[$type] ?? null;
        if (!$scalarType instanceof Type) {
            return new MixedType();
        }
        $types = [$scalarType];
        if ($isNullable) {
            $types[] = new NullType();
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
}
