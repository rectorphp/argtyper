<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\NodeManipulator;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\StrictNativeFunctionReturnTypeAnalyzer;
use Argtyper202511\Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
final class AddReturnTypeFromStrictNativeCall
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\StrictNativeFunctionReturnTypeAnalyzer
     */
    private $strictNativeFunctionReturnTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    public function __construct(StaticTypeMapper $staticTypeMapper, StrictNativeFunctionReturnTypeAnalyzer $strictNativeFunctionReturnTypeAnalyzer, NodeTypeResolver $nodeTypeResolver, TypeFactory $typeFactory, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->strictNativeFunctionReturnTypeAnalyzer = $strictNativeFunctionReturnTypeAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeFactory = $typeFactory;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
    }
    /**
     * @template TFunctionLike as ClassMethod|Function_
     *
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @return TFunctionLike|null
     */
    public function add($functionLike, Scope $scope)
    {
        // already filled, skip
        if ($functionLike->returnType instanceof Node) {
            return null;
        }
        if ($functionLike instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($functionLike, $scope)) {
            return null;
        }
        $nativeCallLikes = $this->strictNativeFunctionReturnTypeAnalyzer->matchAlwaysReturnNativeCallLikes($functionLike);
        if ($nativeCallLikes === null) {
            return null;
        }
        $callLikeTypes = [];
        foreach ($nativeCallLikes as $nativeCallLike) {
            $callLikeTypes[] = $this->nodeTypeResolver->getType($nativeCallLike);
        }
        $returnType = $this->typeFactory->createMixedPassedOrUnionTypeAndKeepConstant($callLikeTypes);
        if ($returnType instanceof MixedType) {
            return null;
        }
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType, TypeKind::RETURN);
        if (!$returnTypeNode instanceof Node) {
            return null;
        }
        $functionLike->returnType = $returnTypeNode;
        return $functionLike;
    }
}
