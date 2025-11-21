<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\NodeManipulator;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Cast;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Argtyper202511\Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
final class AddReturnTypeFromCast
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    public function __construct(BetterNodeFinder $betterNodeFinder, ReturnTypeInferer $returnTypeInferer, StaticTypeMapper $staticTypeMapper, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|null
     */
    public function add($functionLike, Scope $scope)
    {
        if ($functionLike->returnType instanceof Node) {
            return null;
        }
        if ($functionLike instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($functionLike, $scope)) {
            return null;
        }
        $hasNonCastReturn = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($functionLike, static function (Node $subNode) : bool {
            return $subNode instanceof Return_ && !$subNode->expr instanceof Cast;
        });
        if ($hasNonCastReturn) {
            return null;
        }
        $returnType = $this->returnTypeInferer->inferFunctionLike($functionLike);
        if ($returnType instanceof UnionType) {
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
