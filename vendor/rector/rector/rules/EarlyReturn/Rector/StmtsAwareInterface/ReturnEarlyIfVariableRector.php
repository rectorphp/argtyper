<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\EarlyReturn\Rector\StmtsAwareInterface;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Else_;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Argtyper202511\Rector\NodeAnalyzer\VariableAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\StmtsAwareInterface\ReturnEarlyIfVariableRector\ReturnEarlyIfVariableRectorTest
 */
final class ReturnEarlyIfVariableRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\VariableAnalyzer
     */
    private $variableAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(VariableAnalyzer $variableAnalyzer, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->variableAnalyzer = $variableAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace if conditioned variable override with direct return', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($value)
    {
        if ($value === 50) {
            $value = 100;
        }

        return $value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($value)
    {
        if ($value === 50) {
            return 100;
        }

        return $value;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        $stmts = (array) $node->stmts;
        foreach ($stmts as $key => $stmt) {
            $returnVariable = $this->matchNextStmtReturnVariable($node, $key);
            if (!$returnVariable instanceof Variable) {
                continue;
            }
            if ($stmt instanceof If_ && !$stmt->else instanceof Else_ && $stmt->elseifs === []) {
                // is single condition if
                $if = $stmt;
                if (\count($if->stmts) !== 1) {
                    continue;
                }
                $onlyIfStmt = $if->stmts[0];
                $assignedExpr = $this->matchOnlyIfStmtReturnExpr($onlyIfStmt, $returnVariable);
                if (!$assignedExpr instanceof Expr) {
                    continue;
                }
                $if->stmts[0] = new Return_($assignedExpr);
                $this->mirrorComments($if->stmts[0], $onlyIfStmt);
                return $node;
            }
        }
        return null;
    }
    private function matchOnlyIfStmtReturnExpr(Stmt $onlyIfStmt, Variable $returnVariable) : ?\Argtyper202511\PhpParser\Node\Expr
    {
        if (!$onlyIfStmt instanceof Expression) {
            return null;
        }
        if (!$onlyIfStmt->expr instanceof Assign) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($onlyIfStmt);
        if ($phpDocInfo->getVarTagValueNode() instanceof VarTagValueNode) {
            return null;
        }
        $assign = $onlyIfStmt->expr;
        // assign to same variable that is returned
        if (!$assign->var instanceof Variable) {
            return null;
        }
        if ($this->variableAnalyzer->isStaticOrGlobal($assign->var)) {
            return null;
        }
        if ($this->variableAnalyzer->isUsedByReference($assign->var)) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($assign->var, $returnVariable)) {
            return null;
        }
        // return directly
        return $assign->expr;
    }
    private function matchNextStmtReturnVariable(StmtsAwareInterface $stmtsAware, int $key) : ?\Argtyper202511\PhpParser\Node\Expr\Variable
    {
        $nextStmt = $stmtsAware->stmts[$key + 1] ?? null;
        // last item → stop
        if (!$nextStmt instanceof Stmt) {
            return null;
        }
        if (!$nextStmt instanceof Return_) {
            return null;
        }
        // next return must be variable
        if (!$nextStmt->expr instanceof Variable) {
            return null;
        }
        return $nextStmt->expr;
    }
}
