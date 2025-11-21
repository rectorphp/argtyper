<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\RectorPrefix202511\Nette\Utils\Strings;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\Generic\GenericObjectType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeTraverser;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
/**
 * @implements TypeMapperInterface<ObjectType>
 */
final class ObjectTypeMapper implements TypeMapperInterface
{
    public function getNodeClass(): string
    {
        return ObjectType::class;
    }
    /**
     * @param ObjectType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        $type = TypeTraverser::map($type, static function (Type $type, callable $traverse): Type {
            if ($type instanceof ArrayType && ($type->getItemType() instanceof MixedType && $type->getKeyType() instanceof MixedType)) {
                return new ArrayType(new MixedType(), new MixedType(\true));
            }
            if (!$type instanceof ObjectType) {
                return $traverse($type);
            }
            $typeClass = get_class($type);
            // early native ObjectType check
            if ($typeClass === 'PHPStan\Type\ObjectType') {
                return new ObjectType('\\' . $type->getClassName());
            }
            if ($type instanceof FullyQualifiedObjectType) {
                return new ObjectType('\\' . $type->getClassName());
            }
            if ($type instanceof GenericObjectType) {
                return $traverse(new GenericObjectType('\\' . $type->getClassName(), $type->getTypes()), $traverse);
            }
            return $traverse($type, $traverse);
        });
        return $type->toPhpDocNode();
    }
    /**
     * @param ObjectType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind): ?Node
    {
        if ($type instanceof SelfObjectType) {
            return new Name('self');
        }
        if ($type instanceof ShortenedObjectType || $type instanceof AliasedObjectType) {
            return new FullyQualified($type->getFullyQualifiedName());
        }
        if ($type instanceof FullyQualifiedObjectType) {
            $className = $type->getClassName();
            if (strncmp($className, '\\', strlen('\\')) === 0) {
                // skip leading \
                return new FullyQualified(Strings::substring($className, 1));
            }
            return new FullyQualified($className);
        }
        if ($type instanceof NonExistingObjectType) {
            return null;
        }
        return new FullyQualified($type->getClassName());
    }
}
