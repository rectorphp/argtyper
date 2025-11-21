<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\ClassConst;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
/**
 * @implements NodeNameResolverInterface<ClassConst>
 */
final class ClassConstNameResolver implements NodeNameResolverInterface
{
    public function getNode() : string
    {
        return ClassConst::class;
    }
    /**
     * @param ClassConst $node
     */
    public function resolve(Node $node, ?Scope $scope) : ?string
    {
        if ($node->consts === []) {
            return null;
        }
        $onlyConstant = $node->consts[0];
        return $onlyConstant->name->toString();
    }
}
