<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
final class ReturnAnalyzer
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\SilentVoidResolver
     */
    private $silentVoidResolver;
    public function __construct(SilentVoidResolver $silentVoidResolver)
    {
        $this->silentVoidResolver = $silentVoidResolver;
    }
    /**
     * @param Return_[] $returns
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function hasOnlyReturnWithExpr($functionLike, array $returns): bool
    {
        if ($functionLike->stmts === null) {
            return \false;
        }
        // void or combined with yield/yield from
        if ($returns === []) {
            return \false;
        }
        // possible void
        foreach ($returns as $return) {
            if (!$return->expr instanceof Expr) {
                return \false;
            }
        }
        // possible silent void
        return !$this->silentVoidResolver->hasSilentVoid($functionLike);
    }
}
