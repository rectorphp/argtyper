<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeAnalyser;

use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Foreach_;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPUnit\CodeQuality\ValueObject\VariableNameToType;
use Rector\PHPUnit\CodeQuality\ValueObject\VariableNameToTypeCollection;
/**
 * We look for object|null type on the left:
 *
 * $value = $this->getSomething();
 */
final class NullableObjectAssignCollector
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Foreach_ $stmtsAware
     */
    public function collect($stmtsAware): VariableNameToTypeCollection
    {
        $variableNamesToType = [];
        // first round to collect assigns
        foreach ((array) $stmtsAware->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                return new VariableNameToTypeCollection([]);
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $variableNameToType = $this->collectFromAssign($stmt->expr);
            if (!$variableNameToType instanceof VariableNameToType) {
                continue;
            }
            $variableNamesToType[] = $variableNameToType;
        }
        return new VariableNameToTypeCollection($variableNamesToType);
    }
    private function collectFromAssign(Assign $assign): ?VariableNameToType
    {
        if (!$assign->expr instanceof MethodCall) {
            return null;
        }
        if (!$assign->var instanceof Variable) {
            return null;
        }
        $variableType = $this->nodeTypeResolver->getType($assign);
        $bareVariableType = TypeCombinator::removeNull($variableType);
        if (!$bareVariableType instanceof ObjectType) {
            return null;
        }
        $variableName = $this->nodeNameResolver->getName($assign->var);
        if (!is_string($variableName)) {
            return null;
        }
        return new VariableNameToType($variableName, $bareVariableType->getClassName());
    }
}
