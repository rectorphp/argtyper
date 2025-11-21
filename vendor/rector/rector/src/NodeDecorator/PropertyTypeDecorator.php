<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeDecorator;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Type\Generic\GenericObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
final class PropertyTypeDecorator
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
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
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpVersionProvider $phpVersionProvider, StaticTypeMapper $staticTypeMapper, PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function decorate(Property $property, ?Type $type) : void
    {
        if (!$type instanceof Type) {
            return;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            $phpParserType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PROPERTY);
            if ($phpParserType instanceof Node) {
                $property->type = $phpParserType;
                if ($type instanceof GenericObjectType) {
                    $this->phpDocTypeChanger->changeVarType($property, $phpDocInfo, $type);
                }
                return;
            }
        }
        $this->phpDocTypeChanger->changeVarType($property, $phpDocInfo, $type);
    }
}
