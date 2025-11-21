<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\NodeTransformer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpParser\Node\Value\ValueResolver;
final class ConsoleOptionAndArgumentMethodCallVariableReplacer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, ValueResolver $valueResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->valueResolver = $valueResolver;
    }
    public function replace(ClassMethod $executeClassMethod): void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($executeClassMethod->stmts, function (Node $node): ?Variable {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->var, 'input')) {
                return null;
            }
            if (!$this->nodeNameResolver->isNames($node->name, ['getOption', 'getArgument'])) {
                return null;
            }
            $firstArgValue = $node->getArgs()[0]->value;
            if ($firstArgValue instanceof ClassConstFetch || $firstArgValue instanceof ConstFetch) {
                $variableName = $this->valueResolver->getValue($firstArgValue);
                return new Variable(str_replace('-', '_', $variableName));
            }
            if (!$firstArgValue instanceof String_) {
                // unable to resolve argument/option name
                throw new ShouldNotHappenException();
            }
            $variableName = $firstArgValue->value;
            return new Variable(str_replace('-', '_', $variableName));
        });
    }
}
