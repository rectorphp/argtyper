<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\NodeFactory;

use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\Empty_;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\PHPUnit\Enum\ConsecutiveVariable;
final class NestedClosureAssertFactory
{
    /**
     * @return Stmt[]
     */
    public function create(MethodCall $assertMethodCall, int $assertKey): array
    {
        $callableFirstArg = $assertMethodCall->getArgs()[0];
        if ($callableFirstArg->value instanceof ArrowFunction) {
            $arrowFunction = $callableFirstArg->value;
            if ($arrowFunction->expr instanceof Identical) {
                // unwrap closure arrow function to direct assert as more readable
                $identical = $arrowFunction->expr;
                if ($identical->left instanceof Variable) {
                    return $this->createAssertSameParameters($identical->right, $assertKey);
                }
                if ($identical->right instanceof Variable) {
                    return $this->createAssertSameParameters($identical->left, $assertKey);
                }
            }
            if ($arrowFunction->expr instanceof BooleanNot && $arrowFunction->expr->expr instanceof Empty_) {
                return $this->createAssertNotEmpty($assertKey, 'assertNotEmpty');
            }
            if ($arrowFunction->expr instanceof Empty_) {
                return $this->createAssertNotEmpty($assertKey, 'assertEmpty');
            }
        }
        $callbackVariable = new Variable('callback');
        $callbackAssign = new Assign($callbackVariable, $callableFirstArg->value);
        $stmts = [new Expression($callbackAssign)];
        $parametersArrayDimFetch = new ArrayDimFetch(new Variable('parameters'), new Int_($assertKey));
        $callbackFuncCall = new FuncCall($callbackVariable, [new Arg($parametersArrayDimFetch)]);
        // add assert true to the callback
        $assertTrueMethodCall = new MethodCall(new Variable('this'), 'assertTrue', [new Arg($callbackFuncCall)]);
        $stmts[] = new Expression($assertTrueMethodCall);
        return $stmts;
    }
    /**
     * @return Expression[]
     */
    private function createAssertSameParameters(Expr $comparedExpr, int $assertKey): array
    {
        // use assert same directly instead
        $args = [new Arg($comparedExpr), new Arg(new ArrayDimFetch(new Variable('parameters'), new Int_($assertKey)))];
        $assertSameMethodCall = new MethodCall(new Variable('this'), new Identifier('assertSame'), $args);
        return [new Expression($assertSameMethodCall)];
    }
    /**
     * @return Expression[]
     */
    private function createAssertNotEmpty(int $assertKey, string $emptyMethodName): array
    {
        $arrayDimFetch = new ArrayDimFetch(new Variable(ConsecutiveVariable::PARAMETERS), new Int_($assertKey));
        $assertEmptyMethodCall = new MethodCall(new Variable('this'), new Identifier($emptyMethodName), [new Arg($arrayDimFetch)]);
        return [new Expression($assertEmptyMethodCall)];
    }
}
