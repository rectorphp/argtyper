<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\ClassStringType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeTraverser;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
/**
 * @implements TypeMapperInterface<ClassStringType>
 */
final class ClassStringTypeMapper implements TypeMapperInterface
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
        return ClassStringType::class;
    }
    /**
     * @param ClassStringType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        $type = TypeTraverser::map($type, static function (Type $type, callable $traverse) : Type {
            if (!$type instanceof ObjectType) {
                return $traverse($type);
            }
            $typeClass = \get_class($type);
            if ($typeClass === 'PHPStan\\Type\\ObjectType') {
                return new ObjectType('\\' . $type->getClassName());
            }
            return $traverse($type);
        });
        return $type->toPhpDocNode();
    }
    /**
     * @param ClassStringType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }
        return new Identifier('string');
    }
}
