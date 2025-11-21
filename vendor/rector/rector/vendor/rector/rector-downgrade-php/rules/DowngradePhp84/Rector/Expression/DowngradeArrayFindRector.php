<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp84\Rector\Expression;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Break_;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Foreach_;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\Rector\Naming\Naming\VariableNaming;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://php.watch/versions/8.4/array_find-array_find_key-array_any-array_all
 *
 * @see \Rector\Tests\DowngradePhp84\Rector\Expression\DowngradeArrayFindRector\DowngradeArrayFindRectorTest
 */
final class DowngradeArrayFindRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    public function getNodeTypes() : array
    {
        return [Expression::class, Return_::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade array_find() to foreach loop', [new CodeSample(<<<'CODE_SAMPLE'
$found = array_find($animals, fn($animal) => str_starts_with($animal, 'c'));
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$found = null;
foreach ($animals as $animal) {
    if (str_starts_with($animal, 'c')) {
        $found = $animal;
        break;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Expression|Return_ $node
     * @return Stmt[]|null
     */
    public function refactor(Node $node) : ?array
    {
        if ($node instanceof Return_ && !$node->expr instanceof FuncCall) {
            return null;
        }
        if ($node instanceof Expression && !$node->expr instanceof Assign) {
            return null;
        }
        $expr = $node instanceof Expression && $node->expr instanceof Assign ? $node->expr->expr : $node->expr;
        if (!$expr instanceof FuncCall) {
            return null;
        }
        if (!$this->isName($expr, 'array_find')) {
            return null;
        }
        if ($expr->isFirstClassCallable()) {
            return null;
        }
        $args = $expr->getArgs();
        if (\count($args) !== 2) {
            return null;
        }
        if (!$args[1]->value instanceof ArrowFunction) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        $variable = $node instanceof Expression && $node->expr instanceof Assign ? $node->expr->var : new Variable($this->variableNaming->createCountedValueName('found', $scope));
        $valueCond = $args[1]->value->expr;
        $if = new If_($valueCond, ['stmts' => [new Expression(new Assign($variable, $args[1]->value->params[0]->var)), new Break_()]]);
        $result = [
            // init
            new Expression(new Assign($variable, new ConstFetch(new Name('null')))),
            // foreach loop
            new Foreach_($args[0]->value, $args[1]->value->params[0]->var, isset($args[1]->value->params[1]->var) ? ['keyVar' => $args[1]->value->params[1]->var, 'stmts' => [$if]] : ['stmts' => [$if]]),
        ];
        if ($node instanceof Return_) {
            $result[] = new Return_($variable);
        }
        return $result;
    }
}
