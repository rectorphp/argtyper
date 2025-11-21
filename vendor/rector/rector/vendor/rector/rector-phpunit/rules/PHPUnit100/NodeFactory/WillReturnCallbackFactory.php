<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\PHPUnit100\NodeFactory;

use Argtyper202511\PhpParser\BuilderFactory;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\ClosureUse;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Minus;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\PHPUnit\Enum\ConsecutiveVariable;
use Argtyper202511\Rector\PHPUnit\NodeFactory\ConsecutiveIfsFactory;
use Argtyper202511\Rector\PHPUnit\NodeFactory\MatcherInvocationCountMethodCallNodeFactory;
use Argtyper202511\Rector\PHPUnit\NodeFactory\UsedVariablesResolver;
final class WillReturnCallbackFactory
{
    /**
     * @readonly
     * @var \PhpParser\BuilderFactory
     */
    private $builderFactory;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeFactory\UsedVariablesResolver
     */
    private $usedVariablesResolver;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeFactory\MatcherInvocationCountMethodCallNodeFactory
     */
    private $matcherInvocationCountMethodCallNodeFactory;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeFactory\ConsecutiveIfsFactory
     */
    private $consecutiveIfsFactory;
    public function __construct(BuilderFactory $builderFactory, UsedVariablesResolver $usedVariablesResolver, MatcherInvocationCountMethodCallNodeFactory $matcherInvocationCountMethodCallNodeFactory, ConsecutiveIfsFactory $consecutiveIfsFactory)
    {
        $this->builderFactory = $builderFactory;
        $this->usedVariablesResolver = $usedVariablesResolver;
        $this->matcherInvocationCountMethodCallNodeFactory = $matcherInvocationCountMethodCallNodeFactory;
        $this->consecutiveIfsFactory = $consecutiveIfsFactory;
    }
    /**
     * @param \PhpParser\Node\Expr\Variable|\PhpParser\Node\Expr|null $referenceVariable
     */
    public function createClosure(MethodCall $withConsecutiveMethodCall, ?Stmt $returnStmt, $referenceVariable): Closure
    {
        $matcherVariable = new Variable(ConsecutiveVariable::MATCHER);
        $usedVariables = $this->usedVariablesResolver->resolveUsedVariables($withConsecutiveMethodCall, $returnStmt);
        $closureStmts = $this->createParametersMatch($withConsecutiveMethodCall);
        if ($returnStmt instanceof Stmt) {
            $closureStmts[] = $returnStmt;
        }
        $parametersParam = new Param(new Variable(ConsecutiveVariable::PARAMETERS));
        $parametersParam->variadic = \true;
        return new Closure(['byRef' => $this->isByRef($referenceVariable), 'uses' => $this->createClosureUses($matcherVariable, $usedVariables), 'params' => [$parametersParam], 'stmts' => $closureStmts]);
    }
    /**
     * @return Stmt[]
     */
    public function createParametersMatch(MethodCall $withConsecutiveMethodCall): array
    {
        $parametersVariable = new Variable(ConsecutiveVariable::PARAMETERS);
        $firstArg = $withConsecutiveMethodCall->getArgs()[0] ?? null;
        if ($firstArg instanceof Arg && $firstArg->unpack) {
            $assertSameMethodCall = $this->createAssertSameDimFetch($firstArg, $parametersVariable);
            return [new Expression($assertSameMethodCall)];
        }
        $numberOfInvocationsMethodCall = $this->matcherInvocationCountMethodCallNodeFactory->create();
        return $this->consecutiveIfsFactory->createIfs($withConsecutiveMethodCall, $numberOfInvocationsMethodCall);
    }
    private function createAssertSameDimFetch(Arg $firstArg, Variable $variable): MethodCall
    {
        $matcherCountMethodCall = $this->matcherInvocationCountMethodCallNodeFactory->create();
        $currentValueArrayDimFetch = new ArrayDimFetch($firstArg->value, new Minus($matcherCountMethodCall, new Int_(1)));
        $compareArgs = [new Arg($currentValueArrayDimFetch), new Arg($variable)];
        return $this->builderFactory->methodCall(new Variable('this'), 'assertSame', $compareArgs);
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Expr\Variable|null $referenceVariable
     */
    private function isByRef($referenceVariable): bool
    {
        return $referenceVariable instanceof Variable;
    }
    /**
     * @param Variable[] $usedVariables
     * @return ClosureUse[]
     */
    private function createClosureUses(Variable $matcherVariable, array $usedVariables): array
    {
        $uses = [new ClosureUse($matcherVariable)];
        foreach ($usedVariables as $usedVariable) {
            $uses[] = new ClosureUse($usedVariable);
        }
        return $uses;
    }
}
