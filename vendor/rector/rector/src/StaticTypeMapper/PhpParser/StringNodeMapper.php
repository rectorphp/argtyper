<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\StaticTypeMapper\PhpParser;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
/**
 * @implements PhpParserNodeMapperInterface<String_>
 */
final class StringNodeMapper implements PhpParserNodeMapperInterface
{
    public function getNodeType(): string
    {
        return String_::class;
    }
    /**
     * @param String_ $node
     */
    public function mapToPHPStan(Node $node): Type
    {
        return new StringType();
    }
}
