<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\TypeInferer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\Exit_;
use Argtyper202511\PhpParser\Node\Expr\Throw_;
use Argtyper202511\PhpParser\Node\Expr\Yield_;
use Argtyper202511\PhpParser\Node\Expr\YieldFrom;
use Argtyper202511\PhpParser\Node\FunctionLike;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Break_;
use Argtyper202511\PhpParser\Node\Stmt\Case_;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Continue_;
use Argtyper202511\PhpParser\Node\Stmt\Do_;
use Argtyper202511\PhpParser\Node\Stmt\Else_;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Finally_;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\Stmt\Goto_;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PhpParser\Node\Stmt\Switch_;
use Argtyper202511\PhpParser\Node\Stmt\TryCatch;
use Argtyper202511\PhpParser\Node\Stmt\While_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer\NeverFuncCallAnalyzer;
final class SilentVoidResolver
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\NeverFuncCallAnalyzer
     */
    private $neverFuncCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(BetterNodeFinder $betterNodeFinder, ReflectionResolver $reflectionResolver, NeverFuncCallAnalyzer $neverFuncCallAnalyzer, ValueResolver $valueResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->reflectionResolver = $reflectionResolver;
        $this->neverFuncCallAnalyzer = $neverFuncCallAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function hasExclusiveVoid($functionLike): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($functionLike);
        if ($classReflection instanceof ClassReflection && $classReflection->isInterface()) {
            return \false;
        }
        return !(bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($functionLike, function (Node $subNode): bool {
            if ($subNode instanceof Yield_ || $subNode instanceof YieldFrom) {
                return \true;
            }
            return $subNode instanceof Return_ && $subNode->expr instanceof Expr;
        });
    }
    public function hasSilentVoid(FunctionLike $functionLike, bool $withNativeNeverType = \true): bool
    {
        if ($functionLike instanceof ArrowFunction) {
            return \false;
        }
        $stmts = (array) $functionLike->getStmts();
        return !$this->hasStmtsAlwaysReturnOrExit($stmts, $withNativeNeverType);
    }
    /**
     * @param Stmt[]|Expression[] $stmts
     */
    private function hasStmtsAlwaysReturnOrExit(array $stmts, bool $withNativeNeverType): bool
    {
        foreach ($stmts as $stmt) {
            if ($this->neverFuncCallAnalyzer->isWithNeverTypeExpr($stmt, $withNativeNeverType)) {
                return \true;
            }
            if ($this->isStopped($stmt)) {
                return \true;
            }
            // has switch with always return
            if ($stmt instanceof Switch_ && $this->isSwitchWithAlwaysReturnOrExit($stmt, $withNativeNeverType)) {
                return \true;
            }
            if ($stmt instanceof TryCatch && $this->isTryCatchAlwaysReturnOrExit($stmt, $withNativeNeverType)) {
                return \true;
            }
            if ($this->isIfReturn($stmt, $withNativeNeverType)) {
                return \true;
            }
            if (!$this->isDoOrWhileWithAlwaysReturnOrExit($stmt, $withNativeNeverType)) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Stmt\Do_|\PhpParser\Node\Stmt\While_ $node
     */
    private function isFoundLoopControl($node): bool
    {
        $isFoundLoopControl = \false;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($node->stmts, static function (Node $subNode) use (&$isFoundLoopControl) {
            if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($subNode instanceof Break_ || $subNode instanceof Continue_ || $subNode instanceof Goto_) {
                $isFoundLoopControl = \true;
                return NodeVisitor::STOP_TRAVERSAL;
            }
        });
        return $isFoundLoopControl;
    }
    private function isDoOrWhileWithAlwaysReturnOrExit(Stmt $stmt, bool $withNativeNeverType): bool
    {
        if (!$stmt instanceof Do_ && !$stmt instanceof While_) {
            return \false;
        }
        if ($this->valueResolver->isTrue($stmt->cond)) {
            return !$this->isFoundLoopControl($stmt);
        }
        if (!$this->hasStmtsAlwaysReturnOrExit($stmt->stmts, $withNativeNeverType)) {
            return \false;
        }
        return $stmt instanceof Do_ && !$this->isFoundLoopControl($stmt);
    }
    /**
     * @param \PhpParser\Node\Stmt|\PhpParser\Node\Expr $stmt
     */
    private function isIfReturn($stmt, bool $withNativeNeverType): bool
    {
        if (!$stmt instanceof If_) {
            return \false;
        }
        foreach ($stmt->elseifs as $elseIf) {
            if (!$this->hasStmtsAlwaysReturnOrExit($elseIf->stmts, $withNativeNeverType)) {
                return \false;
            }
        }
        if (!$stmt->else instanceof Else_) {
            return \false;
        }
        if (!$this->hasStmtsAlwaysReturnOrExit($stmt->stmts, $withNativeNeverType)) {
            return \false;
        }
        return $this->hasStmtsAlwaysReturnOrExit($stmt->else->stmts, $withNativeNeverType);
    }
    private function isStopped(Stmt $stmt): bool
    {
        if ($stmt instanceof Expression) {
            $stmt = $stmt->expr;
        }
        return $stmt instanceof Throw_ || $stmt instanceof Exit_ || $stmt instanceof Return_ && $stmt->expr instanceof Expr || $stmt instanceof Yield_ || $stmt instanceof YieldFrom;
    }
    private function isSwitchWithAlwaysReturnOrExit(Switch_ $switch, bool $withNativeNeverType): bool
    {
        $hasDefault = \false;
        foreach ($switch->cases as $case) {
            if (!$case->cond instanceof Expr) {
                $hasDefault = $case->stmts !== [];
                break;
            }
        }
        if (!$hasDefault) {
            return \false;
        }
        $casesWithReturnOrExitCount = $this->resolveReturnOrExitCount($switch, $withNativeNeverType);
        $cases = array_filter($switch->cases, static function (Case_ $case): bool {
            return $case->stmts !== [];
        });
        // has same amount of first return or exit nodes as switches
        return count($cases) === $casesWithReturnOrExitCount;
    }
    private function isTryCatchAlwaysReturnOrExit(TryCatch $tryCatch, bool $withNativeNeverType): bool
    {
        $hasReturnOrExitInFinally = $tryCatch->finally instanceof Finally_ && $this->hasStmtsAlwaysReturnOrExit($tryCatch->finally->stmts, $withNativeNeverType);
        if (!$this->hasStmtsAlwaysReturnOrExit($tryCatch->stmts, $withNativeNeverType)) {
            return $hasReturnOrExitInFinally;
        }
        foreach ($tryCatch->catches as $catch) {
            if ($this->hasStmtsAlwaysReturnOrExit($catch->stmts, $withNativeNeverType)) {
                continue;
            }
            if ($hasReturnOrExitInFinally) {
                continue;
            }
            return \false;
        }
        return \true;
    }
    private function resolveReturnOrExitCount(Switch_ $switch, bool $withNativeNeverType): int
    {
        $casesWithReturnCount = 0;
        foreach ($switch->cases as $case) {
            if ($this->hasStmtsAlwaysReturnOrExit($case->stmts, $withNativeNeverType)) {
                ++$casesWithReturnCount;
            }
        }
        return $casesWithReturnCount;
    }
}
