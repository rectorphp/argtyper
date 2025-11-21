<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\NodeFinder;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
final class EmptyReturnNodeFinder
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function hasNoOrEmptyReturns(ClassMethod $classMethod): bool
    {
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($classMethod, Return_::class);
        if ($returns === []) {
            return \true;
        }
        foreach ($returns as $return) {
            if ($return->expr instanceof Expr) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
