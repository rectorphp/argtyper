<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php85\Rector\StmtsAwareInterface;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Pipe;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\VariadicPlaceholder;
use Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Argtyper202511\Rector\NodeAnalyzer\ExprAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/pipe-operator-v3
 * @see \Rector\Tests\Php85\Rector\StmtsAwareInterface\SequentialAssignmentsToPipeOperatorRector\SequentialAssignmentsToPipeOperatorRectorTest
 */
final class SequentialAssignmentsToPipeOperatorRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(ExprAnalyzer $exprAnalyzer)
    {
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Transform sequential assignments to pipe operator syntax', [new CodeSample(<<<'CODE_SAMPLE'
$value = "hello world";
$result1 = function1($value);
$result2 = function2($result1);

$result = function3($result2);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = "hello world";

$result = $value
    |> function1(...)
    |> function2(...)
    |> function3(...);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::PIPE_OPERATOER;
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $hasChanged = \false;
        $statements = $node->stmts;
        $totalStatements = \count($statements) - 1;
        for ($i = 0; $i < $totalStatements; ++$i) {
            $chain = $this->findAssignmentChain($statements, $i);
            if ($chain && \count($chain) >= 2) {
                $this->processAssignmentChain($node, $chain, $i);
                $hasChanged = \true;
                // Skip processed statements
                $i += \count($chain) - 1;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param array<int, Stmt> $statements
     * @return array<int, array{stmt: Stmt, assign: Expr, funcCall: Expr\FuncCall}>|null
     */
    private function findAssignmentChain(array $statements, int $startIndex) : ?array
    {
        $chain = [];
        $currentIndex = $startIndex;
        $totalStatements = \count($statements);
        while ($currentIndex < $totalStatements) {
            $stmt = $statements[$currentIndex];
            if (!$stmt instanceof Expression) {
                break;
            }
            $expr = $stmt->expr;
            if (!$expr instanceof Assign) {
                return null;
            }
            // Check if this is a simple function call with one argument
            if (!$expr->expr instanceof FuncCall) {
                return null;
            }
            $funcCall = $expr->expr;
            if (\count($funcCall->args) !== 1) {
                return null;
            }
            $arg = $funcCall->args[0];
            if (!$arg instanceof Arg) {
                return null;
            }
            if ($currentIndex === $startIndex) {
                // First in chain - must be a variable or simple value
                if (!$arg->value instanceof Variable && !$this->exprAnalyzer->isDynamicExpr($arg->value)) {
                    return null;
                }
                $chain[] = ['stmt' => $stmt, 'assign' => $expr, 'funcCall' => $funcCall];
            } else {
                // Subsequent in chain - must use previous assignment's variable
                $previousAssign = $chain[\count($chain) - 1]['assign'];
                $previousVarName = $this->getName($previousAssign->var);
                if (!$arg->value instanceof Variable || $this->getName($arg->value) !== $previousVarName) {
                    break;
                }
                $chain[] = ['stmt' => $stmt, 'assign' => $expr, 'funcCall' => $funcCall];
            }
            ++$currentIndex;
        }
        return $chain;
    }
    /**
     * @param array<int, array{stmt: Stmt, assign: Expr, funcCall: Expr\FuncCall}> $chain
     */
    private function processAssignmentChain(StmtsAwareInterface $stmtsAware, array $chain, int $startIndex) : void
    {
        $lastAssignment = $chain[\count($chain) - 1]['assign'];
        // Get the initial value from the first function call's argument
        $firstFuncCall = $chain[0]['funcCall'];
        if (!$firstFuncCall instanceof FuncCall) {
            return;
        }
        $firstArg = $firstFuncCall->args[0];
        if (!$firstArg instanceof Arg) {
            return;
        }
        $initialValue = $firstArg->value;
        // Build the pipe chain
        $pipeExpression = $initialValue;
        foreach ($chain as $chainItem) {
            $funcCall = $chainItem['funcCall'];
            $placeholderCall = $this->createPlaceholderCall($funcCall);
            $pipeExpression = new Pipe($pipeExpression, $placeholderCall);
        }
        if (!$lastAssignment instanceof Assign) {
            return;
        }
        // Create the final assignment
        $assign = new Assign($lastAssignment->var, $pipeExpression);
        $finalExpression = new Expression($assign);
        // Replace the statements
        $endIndex = $startIndex + \count($chain) - 1;
        // Remove all intermediate statements and replace with the final pipe expression
        for ($i = $startIndex; $i <= $endIndex; ++$i) {
            if ($i === $startIndex) {
                $stmtsAware->stmts[$i] = $finalExpression;
            } else {
                unset($stmtsAware->stmts[$i]);
            }
        }
        $stmts = \array_values($stmtsAware->stmts);
        // Reindex the array
        $stmtsAware->stmts = $stmts;
    }
    private function createPlaceholderCall(FuncCall $funcCall) : FuncCall
    {
        return new FuncCall($funcCall->name, [new VariadicPlaceholder()]);
    }
}
