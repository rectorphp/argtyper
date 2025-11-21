<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\NodeVisitorAbstract;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
final class NameNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $node->name->setAttribute(AttributeKey::IS_FUNCCALL_NAME, \true);
            return null;
        }
        if ($node instanceof ConstFetch) {
            $node->name->setAttribute(AttributeKey::IS_CONSTFETCH_NAME, \true);
            return null;
        }
        if ($node instanceof New_ && $node->class instanceof Name) {
            $node->class->setAttribute(AttributeKey::IS_NEW_INSTANCE_NAME, \true);
            return null;
        }
        if (!$node instanceof StaticCall) {
            return null;
        }
        if (!$node->class instanceof Name) {
            return null;
        }
        $node->class->setAttribute(AttributeKey::IS_STATICCALL_CLASS_NAME, \true);
        return null;
    }
}
