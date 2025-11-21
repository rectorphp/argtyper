<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\Rector\Catch_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Finally_;
use Argtyper202511\PhpParser\Node\Stmt\TryCatch;
use Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Argtyper202511\Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer;
use Argtyper202511\Rector\NodeManipulator\StmtsManipulator;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector\RemoveUnusedVariableInCatchRectorTest
 */
final class RemoveUnusedVariableInCatchRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\StmtsManipulator
     */
    private $stmtsManipulator;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer
     */
    private $exprUsedInNodeAnalyzer;
    public function __construct(StmtsManipulator $stmtsManipulator, BetterNodeFinder $betterNodeFinder, ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer)
    {
        $this->stmtsManipulator = $stmtsManipulator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->exprUsedInNodeAnalyzer = $exprUsedInNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused variable in `catch()`', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        try {
        } catch (Throwable $notUsedThrowable) {
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        try {
        } catch (Throwable) {
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof TryCatch) {
                continue;
            }
            foreach ($stmt->catches as $catch) {
                $caughtVar = $catch->var;
                if (!$caughtVar instanceof Variable) {
                    continue;
                }
                /** @var string $variableName */
                $variableName = $this->getName($caughtVar);
                $isFoundInCatchStmts = (bool) $this->betterNodeFinder->findFirst(array_merge($catch->stmts, $stmt->finally instanceof Finally_ ? $stmt->finally->stmts : []), function (Node $subNode) use ($caughtVar): bool {
                    return $this->exprUsedInNodeAnalyzer->isUsed($subNode, $caughtVar);
                });
                if ($isFoundInCatchStmts) {
                    continue;
                }
                if ($this->stmtsManipulator->isVariableUsedInNextStmt($node, $key + 1, $variableName)) {
                    continue;
                }
                $catch->var = null;
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::NON_CAPTURING_CATCH;
    }
}
