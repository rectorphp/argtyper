<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PHPStan\Type\NeverType;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class NeverFuncCallAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function hasNeverFuncCall($functionLike): bool
    {
        foreach ((array) $functionLike->stmts as $stmt) {
            if ($this->isWithNeverTypeExpr($stmt)) {
                return \true;
            }
        }
        return \false;
    }
    public function isWithNeverTypeExpr(Stmt $stmt, bool $withNativeNeverType = \true): bool
    {
        if ($stmt instanceof Expression) {
            $stmt = $stmt->expr;
        }
        if ($stmt instanceof Stmt) {
            return \false;
        }
        $stmtType = $withNativeNeverType ? $this->nodeTypeResolver->getNativeType($stmt) : $this->nodeTypeResolver->getType($stmt);
        return $stmtType instanceof NeverType;
    }
}
