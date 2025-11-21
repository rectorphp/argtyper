<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\NodeTransformer;

use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class CommandUnusedInputOutputRemover
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var string[]
     */
    private const VARIABLE_NAMES = ['input', 'output'];
    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function remove(ClassMethod $executeClassMethod): void
    {
        foreach (self::VARIABLE_NAMES as $variableName) {
            $inputVariable = $this->betterNodeFinder->findVariableOfName($executeClassMethod->stmts, $variableName);
            // is used → skip
            if ($inputVariable instanceof Variable) {
                continue;
            }
            $this->removeParameterByName($executeClassMethod, $variableName);
        }
    }
    private function removeParameterByName(ClassMethod $classMethod, string $paramName): void
    {
        foreach ($classMethod->getParams() as $key => $param) {
            if (!$this->nodeNameResolver->isName($param->var, $paramName)) {
                continue;
            }
            unset($classMethod->params[$key]);
        }
    }
}
