<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\NodeFactory;

use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\Rector\Exception\NotImplementedYetException;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PHPUnit\CodeQuality\NodeFactory\NestedClosureAssertFactory;
use Argtyper202511\Rector\PHPUnit\Enum\ConsecutiveVariable;
final class ConsecutiveIfsFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PHPUnit\CodeQuality\NodeFactory\NestedClosureAssertFactory
     */
    private $nestedClosureAssertFactory;
    public function __construct(NodeNameResolver $nodeNameResolver, NestedClosureAssertFactory $nestedClosureAssertFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nestedClosureAssertFactory = $nestedClosureAssertFactory;
    }
    /**
     * @return Stmt[]
     */
    public function createIfs(MethodCall $withConsecutiveMethodCall, MethodCall $numberOfInvocationsMethodCall) : array
    {
        $ifs = [];
        $parametersVariable = new Variable(ConsecutiveVariable::PARAMETERS);
        foreach ($withConsecutiveMethodCall->getArgs() as $key => $withConsecutiveArg) {
            $ifStmts = [];
            if ($withConsecutiveArg->value instanceof Array_) {
                $array = $withConsecutiveArg->value;
                foreach ($array->items as $assertKey => $assertArrayItem) {
                    if (!$assertArrayItem instanceof ArrayItem) {
                        continue;
                    }
                    if (!$assertArrayItem->value instanceof MethodCall) {
                        $parametersDimFetch = new ArrayDimFetch(new Variable('parameters'), new Int_($assertKey));
                        $args = [new Arg($assertArrayItem->value), new Arg($parametersDimFetch)];
                        $ifStmts[] = new Expression(new MethodCall(new Variable('this'), 'assertSame', $args));
                        continue;
                    }
                    $assertMethodCall = $assertArrayItem->value;
                    if ($this->nodeNameResolver->isName($assertMethodCall->name, 'equalTo')) {
                        $ifStmts[] = $this->createAssertMethodCall($assertMethodCall, $parametersVariable, $assertKey);
                    } elseif ($this->nodeNameResolver->isName($assertMethodCall->name, 'callback')) {
                        $ifStmts = \array_merge($ifStmts, $this->nestedClosureAssertFactory->create($assertMethodCall, $assertKey));
                    } else {
                        $args = [new Arg($assertMethodCall), new Arg(new ArrayDimFetch(new Variable('parameters'), new Int_($assertKey)))];
                        $assertSameMethodCall = new MethodCall(new Variable('this'), new Identifier('assertSame'), $args);
                        $ifStmts[] = new Expression($assertSameMethodCall);
                    }
                }
            } elseif ($withConsecutiveArg->value instanceof MethodCall) {
                $methodCall = $withConsecutiveArg->value;
                if ($this->nodeNameResolver->isName($methodCall->name, 'callback')) {
                    // special callable case
                    $firstArg = $methodCall->getArgs()[0];
                    if ($firstArg->value instanceof ArrowFunction) {
                        $arrowFunction = $firstArg->value;
                        if ($arrowFunction->expr instanceof Identical) {
                            $identicalCompare = $arrowFunction->expr;
                            // @todo improve in time
                            if ($identicalCompare->left instanceof Variable) {
                                $parametersArrayDimFetch = new ArrayDimFetch(new Variable('parameters'), new Int_(0));
                                $assertSameMethodCall = new MethodCall(new Variable('this'), new Identifier('assertSame'));
                                $assertSameMethodCall->args[] = new Arg($identicalCompare->right);
                                $assertSameMethodCall->args[] = new Arg($parametersArrayDimFetch);
                                return [new Expression($assertSameMethodCall)];
                            }
                        }
                    }
                }
                throw new NotImplementedYetException();
            }
            $ifs[] = new If_(new Identical($numberOfInvocationsMethodCall, new Int_($key + 1)), ['stmts' => $ifStmts]);
        }
        return $ifs;
    }
    private function createAssertMethodCall(MethodCall $assertMethodCall, Variable $parametersVariable, int $parameterPositionKey) : Expression
    {
        $assertMethodCall->name = new Identifier('assertEquals');
        $parametersArrayDimFetch = new ArrayDimFetch($parametersVariable, new Int_($parameterPositionKey));
        $assertMethodCall->args[] = new Arg($parametersArrayDimFetch);
        return new Expression($assertMethodCall);
    }
}
