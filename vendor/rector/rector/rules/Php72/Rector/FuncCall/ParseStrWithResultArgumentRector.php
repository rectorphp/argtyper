<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php72\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector\ParseStrWithResultArgumentRectorTest
 */
final class ParseStrWithResultArgumentRector extends AbstractRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::RESULT_ARG_IN_PARSE_STR;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use $result argument in parse_str() function', [new CodeSample(<<<'CODE_SAMPLE'
parse_str($this->query);
$data = get_defined_vars();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
parse_str($this->query, $result);
$data = $result;
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
    public function refactor(Node $node) : ?StmtsAwareInterface
    {
        return $this->processStrWithResult($node, \false);
    }
    private function processStrWithResult(StmtsAwareInterface $stmtsAware, bool $hasChanged, int $jumpToKey = 0) : ?\Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface
    {
        if ($stmtsAware->stmts === null) {
            return null;
        }
        \end($stmtsAware->stmts);
        $totalKeys = \key($stmtsAware->stmts);
        \reset($stmtsAware->stmts);
        for ($key = $jumpToKey; $key < $totalKeys; ++$key) {
            if (!isset($stmtsAware->stmts[$key], $stmtsAware->stmts[$key + 1])) {
                break;
            }
            $stmt = $stmtsAware->stmts[$key];
            if ($this->shouldSkip($stmt)) {
                continue;
            }
            /** @var Expression $stmt */
            /** @var FuncCall $expr */
            $expr = $stmt->expr;
            $resultVariable = new Variable('result');
            $expr->args[1] = new Arg($resultVariable);
            $nextExpression = $stmtsAware->stmts[$key + 1];
            $this->traverseNodesWithCallable($nextExpression, function (Node $node) use($resultVariable, &$hasChanged) : ?Variable {
                if (!$node instanceof FuncCall) {
                    return null;
                }
                if (!$this->isName($node, 'get_defined_vars')) {
                    return null;
                }
                $hasChanged = \true;
                return $resultVariable;
            });
            return $this->processStrWithResult($stmtsAware, $hasChanged, $key + 2);
        }
        if ($hasChanged) {
            return $stmtsAware;
        }
        return null;
    }
    private function shouldSkip(Stmt $stmt) : bool
    {
        if (!$stmt instanceof Expression) {
            return \true;
        }
        if (!$stmt->expr instanceof FuncCall) {
            return \true;
        }
        if (!$this->isName($stmt->expr, 'parse_str')) {
            return \true;
        }
        return isset($stmt->expr->args[1]);
    }
}
