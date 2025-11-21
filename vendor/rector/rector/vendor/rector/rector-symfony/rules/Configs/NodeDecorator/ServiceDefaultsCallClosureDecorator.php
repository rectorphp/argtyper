<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Configs\NodeDecorator;

use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
final class ServiceDefaultsCallClosureDecorator
{
    public function decorate(Closure $closure, string $methodName) : void
    {
        foreach ($closure->stmts as $key => $nodeStmt) {
            if (!$nodeStmt instanceof Expression) {
                continue;
            }
            if (!$nodeStmt->expr instanceof Assign) {
                continue;
            }
            $assign = $nodeStmt->expr;
            if (!$assign->var instanceof Variable) {
                continue;
            }
            if ($assign->var->name !== 'services') {
                continue;
            }
            $servicesVariable = $assign->var;
            // add defaults here, right after assign :)
            $autoconfigureExpression = $this->createDefaultsAutoconfigureExpression($methodName);
            \array_splice($closure->stmts, $key + 1, 0, [$autoconfigureExpression]);
            break;
        }
    }
    public function createDefaultsAutoconfigureExpression(string $methodName) : Expression
    {
        $defaultsMethodCall = new MethodCall(new Variable('services'), 'defaults');
        $autoconfigureMethodCall = new MethodCall($defaultsMethodCall, $methodName);
        return new Expression($autoconfigureMethodCall);
    }
}
