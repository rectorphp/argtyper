<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Coalesce;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticPropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
final class CoalesceAnalyzer
{
    /**
     * @var array<class-string<Expr>>
     */
    private const ISSETABLE_EXPR = [Variable::class, ArrayDimFetch::class, PropertyFetch::class, StaticPropertyFetch::class];
    public function hasIssetableLeft(Coalesce $coalesce) : bool
    {
        if ($coalesce->left instanceof Coalesce) {
            return \true;
        }
        $leftClass = \get_class($coalesce->left);
        return \in_array($leftClass, self::ISSETABLE_EXPR, \true);
    }
}
