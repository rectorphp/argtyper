<?php

declare (strict_types=1);
namespace Rector\ArgTyper\Rector\Rector\Function_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\NullableType;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PHPStan\Type\NeverType;
use Argtyper202511\PHPStan\Type\NullType;
use Argtyper202511\PHPStan\Type\ResourceType;
use Rector\ArgTyper\Configuration\FuncCallTypesConfigurationProvider;
use Rector\ArgTyper\Exception\NotImplementedException;
use Rector\ArgTyper\Rector\NodeTypeChecker;
use Rector\ArgTyper\Rector\TypeResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
/**
 * @see \Rector\ArgTyper\Tests\Rector\Rector\Function_\AddFunctionParamTypeRector\AddFunctionParamTypeRectorTest
 */
final class AddFunctionParamTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\ArgTyper\Configuration\FuncCallTypesConfigurationProvider
     */
    private $funcCallTypesConfigurationProvider;
    public function __construct(FuncCallTypesConfigurationProvider $funcCallTypesConfigurationProvider)
    {
        $this->funcCallTypesConfigurationProvider = $funcCallTypesConfigurationProvider;
    }
    public function getNodeTypes() : array
    {
        return [Function_::class];
    }
    /**
     * @param Function_ $node
     */
    public function refactor(Node $node) : ?Function_
    {
        if ($node->getParams() === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getParams() as $position => $param) {
            $functionTypesByPosition = $this->funcCallTypesConfigurationProvider->matchByPosition($node);
            if ($functionTypesByPosition === []) {
                continue;
            }
            $paramFunctionTypes = $functionTypesByPosition[$position] ?? null;
            if ($paramFunctionTypes === null) {
                continue;
            }
            if (\count($paramFunctionTypes) >= 2) {
                throw new NotImplementedException('Multiple types not implemented yet');
            }
            $paramFunctionType = $paramFunctionTypes[0];
            // nothing useful
            if (\in_array($paramFunctionType->getType(), [NullType::class, ResourceType::class, NeverType::class])) {
                continue;
            }
            $isNullable = NodeTypeChecker::isParamNullable($param);
            $typeNode = TypeResolver::resolveTypeNode($paramFunctionType->getType());
            if ($paramFunctionType->isObjectType() && ($param->type instanceof Name || $param->type instanceof NullableType && $param->type->type instanceof Name)) {
                // skip already set object type
                continue;
            }
            if ($isNullable) {
                $param->type = new NullableType($typeNode);
                $hasChanged = \true;
            } else {
                $param->type = $typeNode;
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
