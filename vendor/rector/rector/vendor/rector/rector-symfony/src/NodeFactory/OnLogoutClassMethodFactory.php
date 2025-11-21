<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\NodeFactory;

use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\NodeAnalyzer\ParamAnalyzer;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
final class OnLogoutClassMethodFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\BareLogoutClassMethodFactory
     */
    private $bareLogoutClassMethodFactory;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    /**
     * @var array<string, string>
     */
    private const PARAMETER_TO_GETTER_NAMES = ['request' => 'getRequest', 'response' => 'getResponse', 'token' => 'getToken'];
    public function __construct(NodeNameResolver $nodeNameResolver, \Argtyper202511\Rector\Symfony\NodeFactory\BareLogoutClassMethodFactory $bareLogoutClassMethodFactory, ParamAnalyzer $paramAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->bareLogoutClassMethodFactory = $bareLogoutClassMethodFactory;
        $this->paramAnalyzer = $paramAnalyzer;
    }
    public function createFromLogoutClassMethod(ClassMethod $logoutClassMethod): ClassMethod
    {
        $classMethod = $this->bareLogoutClassMethodFactory->create();
        $assignStmts = $this->createAssignStmtFromOldClassMethod($logoutClassMethod);
        $classMethod->stmts = array_merge($assignStmts, (array) $logoutClassMethod->stmts);
        return $classMethod;
    }
    /**
     * @return Stmt[]
     */
    private function createAssignStmtFromOldClassMethod(ClassMethod $onLogoutSuccessClassMethod): array
    {
        $usedParams = $this->resolveUsedParams($onLogoutSuccessClassMethod);
        return $this->createAssignStmts($usedParams);
    }
    /**
     * @return Param[]
     */
    private function resolveUsedParams(ClassMethod $logoutClassMethod): array
    {
        $usedParams = [];
        foreach ($logoutClassMethod->params as $oldParam) {
            if (!$this->paramAnalyzer->isParamUsedInClassMethod($logoutClassMethod, $oldParam)) {
                continue;
            }
            $usedParams[] = $oldParam;
        }
        return $usedParams;
    }
    /**
     * @param Param[] $params
     * @return Expression[]
     */
    private function createAssignStmts(array $params): array
    {
        $logoutEventVariable = new Variable('logoutEvent');
        $assignStmts = [];
        foreach ($params as $param) {
            foreach (self::PARAMETER_TO_GETTER_NAMES as $parameterName => $getterName) {
                if (!$this->nodeNameResolver->isName($param, $parameterName)) {
                    continue;
                }
                $assign = new Assign($param->var, new MethodCall($logoutEventVariable, $getterName));
                $assignStmts[] = new Expression($assign);
            }
        }
        return $assignStmts;
    }
}
