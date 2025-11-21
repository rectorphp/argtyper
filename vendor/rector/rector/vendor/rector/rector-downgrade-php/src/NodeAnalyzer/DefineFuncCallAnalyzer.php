<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Rector\NodeNameResolver\NodeNameResolver;
final class DefineFuncCallAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param string[] $constants
     */
    public function isDefinedWithConstants(Expr $expr, array $constants): bool
    {
        if (!$expr instanceof FuncCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($expr, 'defined')) {
            return \false;
        }
        if ($expr->isFirstClassCallable()) {
            return \false;
        }
        $firstArg = $expr->getArgs()[0];
        if (!$firstArg->value instanceof String_) {
            return \false;
        }
        $string = $firstArg->value;
        return in_array($string->value, $constants, \true);
    }
}
