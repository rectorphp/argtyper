<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclarationDocblocks\NodeFinder;

use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
final class ReturnNodeFinder
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer
     */
    private $returnAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, ReturnAnalyzer $returnAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->returnAnalyzer = $returnAnalyzer;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function findOnlyReturnWithExpr($functionLike) : ?Return_
    {
        $returnsScoped = $this->betterNodeFinder->findReturnsScoped($functionLike);
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($functionLike, $returnsScoped)) {
            return null;
        }
        if (\count($returnsScoped) !== 1) {
            return null;
        }
        return $returnsScoped[0];
    }
}
