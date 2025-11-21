<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
/**
 * @implements TypeMapperInterface<BooleanType>
 */
final class BooleanTypeMapper implements TypeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getNodeClass() : string
    {
        return BooleanType::class;
    }
    /**
     * @param BooleanType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param BooleanType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }
        if ($typeKind === TypeKind::PROPERTY) {
            return new Identifier('bool');
        }
        if ($typeKind === TypeKind::UNION && $type->isFalse()->yes()) {
            return new Identifier('false');
        }
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::NULL_FALSE_TRUE_STANDALONE_TYPE)) {
            return new Identifier('bool');
        }
        if ($type->isTrue()->yes()) {
            return new Identifier('true');
        }
        if ($type->isFalse()->yes()) {
            return new Identifier('false');
        }
        return new Identifier('bool');
    }
}
