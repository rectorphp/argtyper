<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\NodeVisitorAbstract;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
final class ArgNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    public function enterNode(Node $node): ?Node
    {
        if (!$node instanceof FuncCall) {
            return null;
        }
        if (!$node->name instanceof Name) {
            return null;
        }
        $funcCallName = $node->name->toString();
        foreach ($node->args as $arg) {
            if (!$arg instanceof Arg) {
                continue;
            }
            if ($arg->value instanceof Array_) {
                $arg->value->setAttribute(AttributeKey::FROM_FUNC_CALL_NAME, $funcCallName);
                continue;
            }
            if ($arg->value instanceof ArrayDimFetch) {
                $arg->value->setAttribute(AttributeKey::FROM_FUNC_CALL_NAME, $funcCallName);
            }
        }
        return null;
    }
}
