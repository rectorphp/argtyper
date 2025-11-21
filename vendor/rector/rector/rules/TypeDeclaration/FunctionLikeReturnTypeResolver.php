<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration;

use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
final class FunctionLikeReturnTypeResolver
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function resolveFunctionLikeReturnTypeToPHPStanType(ClassMethod $classMethod) : Type
    {
        $functionReturnType = $classMethod->getReturnType();
        if ($functionReturnType === null) {
            return new MixedType();
        }
        return $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionReturnType);
    }
}
