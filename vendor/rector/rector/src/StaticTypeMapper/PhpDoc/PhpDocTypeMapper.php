<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\StaticTypeMapper\PhpDoc;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\Analyser\NameScope;
use Argtyper202511\PHPStan\PhpDoc\TypeNodeResolver;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\StaticTypeMapper\PhpDoc\PhpDocTypeMapperTest
 */
final class PhpDocTypeMapper
{
    /**
     * @var PhpDocTypeMapperInterface[]
     * @readonly
     */
    private $phpDocTypeMappers;
    /**
     * @readonly
     * @var \PHPStan\PhpDoc\TypeNodeResolver
     */
    private $typeNodeResolver;
    /**
     * @param PhpDocTypeMapperInterface[] $phpDocTypeMappers
     */
    public function __construct(array $phpDocTypeMappers, TypeNodeResolver $typeNodeResolver)
    {
        $this->phpDocTypeMappers = $phpDocTypeMappers;
        $this->typeNodeResolver = $typeNodeResolver;
        Assert::notEmpty($phpDocTypeMappers);
    }
    public function mapToPHPStanType(TypeNode $typeNode, Node $node, NameScope $nameScope): Type
    {
        foreach ($this->phpDocTypeMappers as $phpDocTypeMapper) {
            if (!is_a($typeNode, $phpDocTypeMapper->getNodeType())) {
                continue;
            }
            return $phpDocTypeMapper->mapToPHPStanType($typeNode, $node, $nameScope);
        }
        // fallback to PHPStan resolver
        return $this->typeNodeResolver->resolve($typeNode, $nameScope);
    }
}
