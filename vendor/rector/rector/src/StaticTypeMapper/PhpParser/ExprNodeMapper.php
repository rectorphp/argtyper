<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\StaticTypeMapper\PhpParser;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
/**
 * @implements PhpParserNodeMapperInterface<Expr>
 */
final class ExprNodeMapper implements PhpParserNodeMapperInterface
{
    public function getNodeType(): string
    {
        return Expr::class;
    }
    /**
     * @param Expr $node
     */
    public function mapToPHPStan(Node $node): Type
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return new MixedType();
        }
        return $scope->getType($node);
    }
}
