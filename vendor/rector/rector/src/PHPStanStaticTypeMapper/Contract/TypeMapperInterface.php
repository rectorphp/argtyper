<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\Contract;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\ComplexType;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
/**
 * @template TType of Type
 */
interface TypeMapperInterface
{
    /**
     * @return class-string<TType>
     */
    public function getNodeClass(): string;
    /**
     * @param TType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode;
    /**
     * @param TType $type
     * @param TypeKind::* $typeKind
     * @return Name|ComplexType|Identifier|null
     */
    public function mapToPhpParserNode(Type $type, string $typeKind): ?Node;
}
