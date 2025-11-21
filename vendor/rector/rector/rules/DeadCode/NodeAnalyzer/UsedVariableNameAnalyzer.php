<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
final class UsedVariableNameAnalyzer
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
    public function isVariableNamed(Node $node, Variable $variable) : bool
    {
        if (($node instanceof MethodCall || $node instanceof PropertyFetch) && ($node->name instanceof Variable && \is_string($node->name->name))) {
            return $this->nodeNameResolver->isName($variable, $node->name->name);
        }
        if (!$node instanceof Variable) {
            return \false;
        }
        return $this->nodeNameResolver->areNamesEqual($variable, $node);
    }
}
