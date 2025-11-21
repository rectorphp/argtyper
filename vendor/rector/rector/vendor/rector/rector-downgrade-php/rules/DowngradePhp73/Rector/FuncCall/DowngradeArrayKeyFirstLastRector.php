<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp73\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanOr;
use Argtyper202511\PhpParser\Node\Expr\CallLike;
use Argtyper202511\PhpParser\Node\Expr\Cast\Array_;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\NullsafeMethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Echo_;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PhpParser\Node\Stmt\Switch_;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Argtyper202511\Rector\Naming\Naming\VariableNaming;
use Argtyper202511\Rector\NodeAnalyzer\ExprInTopStmtMatcher;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/array_key_first_last
 *
 * @see \Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector\DowngradeArrayKeyFirstLastRectorTest
 */
final class DowngradeArrayKeyFirstLastRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprInTopStmtMatcher
     */
    private $exprInTopStmtMatcher;
    public function __construct(VariableNaming $variableNaming, ExprInTopStmtMatcher $exprInTopStmtMatcher)
    {
        $this->variableNaming = $variableNaming;
        $this->exprInTopStmtMatcher = $exprInTopStmtMatcher;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Downgrade array_key_first() and array_key_last() functions', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        $firstItemKey = array_key_first($items);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        reset($items);
        $firstItemKey = key($items);
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
        return [StmtsAwareInterface::class, Switch_::class, Return_::class, Expression::class, Echo_::class];
    }
    /**
     * @param StmtsAwareInterface|Switch_|Return_|Expression|Echo_ $node
     * @return Node[]|null
     */
    public function refactor(Node $node): ?array
    {
        $exprArrayKeyFirst = $this->exprInTopStmtMatcher->match($node, function (Node $subNode): bool {
            if (!$subNode instanceof FuncCall) {
                return \false;
            }
            return $this->isName($subNode, 'array_key_first');
        });
        if ($exprArrayKeyFirst instanceof FuncCall) {
            return $this->refactorArrayKeyFirst($exprArrayKeyFirst, $node);
        }
        $exprArrayKeyLast = $this->exprInTopStmtMatcher->match($node, function (Node $subNode): bool {
            if (!$subNode instanceof FuncCall) {
                return \false;
            }
            return $this->isName($subNode, 'array_key_last');
        });
        if ($exprArrayKeyLast instanceof FuncCall) {
            return $this->refactorArrayKeyLast($exprArrayKeyLast, $node);
        }
        return null;
    }
    private function resolveVariableFromCallLikeScope(CallLike $callLike, ?Scope $scope): Variable
    {
        /** @var MethodCall|FuncCall|StaticCall|New_|NullsafeMethodCall $callLike */
        if ($callLike instanceof New_) {
            $variableName = (string) $this->getName($callLike->class);
        } else {
            $variableName = (string) $this->getName($callLike->name);
        }
        if ($variableName === '') {
            $variableName = 'array';
        }
        return new Variable($this->variableNaming->createCountedValueName($variableName, $scope));
    }
    /**
     * @return Node[]|null
     * @param \Rector\Contract\PhpParser\Node\StmtsAwareInterface|\PhpParser\Node\Stmt\Switch_|\PhpParser\Node\Stmt\Return_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Echo_ $stmt
     */
    private function refactorArrayKeyFirst(FuncCall $funcCall, $stmt): ?array
    {
        $args = $funcCall->getArgs();
        if (!isset($args[0])) {
            return null;
        }
        $originalArray = $args[0]->value;
        $array = $this->resolveCastedArray($originalArray);
        $newStmts = [];
        if ($originalArray instanceof CallLike) {
            $scope = $originalArray->getAttribute(AttributeKey::SCOPE);
            $array = $this->resolveVariableFromCallLikeScope($originalArray, $scope);
        }
        if ($originalArray !== $array) {
            $newStmts[] = new Expression(new Assign($array, $originalArray));
        }
        $resetFuncCall = $this->nodeFactory->createFuncCall('reset', [$array]);
        $newStmts[] = $this->resolvePrependNewStmt($array, $resetFuncCall, $stmt);
        $funcCall->name = new Name('key');
        if ($originalArray !== $array) {
            $firstArg = $args[0];
            $firstArg->value = $array;
        }
        $newStmts[] = $stmt;
        return $newStmts;
    }
    /**
     * @return Node[]|null
     * @param \Rector\Contract\PhpParser\Node\StmtsAwareInterface|\PhpParser\Node\Stmt\Switch_|\PhpParser\Node\Stmt\Return_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Echo_ $stmt
     */
    private function refactorArrayKeyLast(FuncCall $funcCall, $stmt): ?array
    {
        $args = $funcCall->getArgs();
        $firstArg = $args[0] ?? null;
        if (!$firstArg instanceof Arg) {
            return null;
        }
        $originalArray = $firstArg->value;
        $array = $this->resolveCastedArray($originalArray);
        $newStmts = [];
        if ($originalArray instanceof CallLike) {
            $scope = $originalArray->getAttribute(AttributeKey::SCOPE);
            $array = $this->resolveVariableFromCallLikeScope($originalArray, $scope);
        }
        if ($originalArray !== $array) {
            $newStmts[] = new Expression(new Assign($array, $originalArray));
        }
        $endFuncCall = $this->nodeFactory->createFuncCall('end', [$array]);
        $newStmts[] = $this->resolvePrependNewStmt($array, $endFuncCall, $stmt);
        $funcCall->name = new Name('key');
        if ($originalArray !== $array) {
            $firstArg->value = $array;
        }
        $newStmts[] = $stmt;
        $resetExpression = new Expression($this->nodeFactory->createFuncCall('reset', [$array]));
        if ($stmt instanceof StmtsAwareInterface) {
            $stmt->stmts = array_merge([$resetExpression], $stmt->stmts);
        } elseif (!$stmt instanceof Return_) {
            $newStmts[] = $resetExpression;
        }
        return $newStmts;
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Expr\Variable $array
     * @param \PhpParser\Node\Stmt|\Rector\Contract\PhpParser\Node\StmtsAwareInterface $stmt
     * @return \PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\If_
     */
    private function resolvePrependNewStmt($array, FuncCall $funcCall, $stmt)
    {
        if (!$stmt instanceof If_ || $stmt->cond instanceof FuncCall || !$stmt->cond instanceof BooleanOr) {
            return new Expression($funcCall);
        }
        $if = new If_($this->nodeFactory->createFuncCall('is_array', [$array]));
        $if->stmts[] = new Expression($funcCall);
        return $if;
    }
    /**
     * @return \PhpParser\Node\Expr|\PhpParser\Node\Expr\Variable
     */
    private function resolveCastedArray(Expr $expr)
    {
        if (!$expr instanceof Array_) {
            return $expr;
        }
        if ($expr->expr instanceof Array_) {
            return $this->resolveCastedArray($expr->expr);
        }
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        $variableName = $this->variableNaming->createCountedValueName((string) $this->getName($expr->expr), $scope);
        return new Variable($variableName);
    }
}
