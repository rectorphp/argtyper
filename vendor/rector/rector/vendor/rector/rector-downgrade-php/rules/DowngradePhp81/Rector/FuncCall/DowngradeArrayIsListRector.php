<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp81\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\CallLike;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Echo_;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PhpParser\Node\Stmt\Switch_;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\NodeAnalyzer\ExprInTopStmtMatcher;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\PhpParser\Parser\InlineCodeParser;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/is_list
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector\DowngradeArrayIsListRectorTest
 */
final class DowngradeArrayIsListRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Parser\InlineCodeParser
     */
    private $inlineCodeParser;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprInTopStmtMatcher
     */
    private $exprInTopStmtMatcher;
    /**
     * @var \PhpParser\Node\Expr\Closure|null
     */
    private $cachedClosure;
    public function __construct(InlineCodeParser $inlineCodeParser, ExprInTopStmtMatcher $exprInTopStmtMatcher)
    {
        $this->inlineCodeParser = $inlineCodeParser;
        $this->exprInTopStmtMatcher = $exprInTopStmtMatcher;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace array_is_list() function', [new CodeSample(<<<'CODE_SAMPLE'
array_is_list([1 => 'apple', 'orange']);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$arrayIsList = function (array $array) : bool {
    if (function_exists('array_is_list')) {
        return array_is_list($array);
    }

    if ($array === []) {
        return true;
    }

    $current_key = 0;
    foreach ($array as $key => $noop) {
        if ($key !== $current_key) {
            return false;
        }
        ++$current_key;
    }

    return true;
};
$arrayIsList([1 => 'apple', 'orange']);
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
        $expr = $this->exprInTopStmtMatcher->match($node, function (Node $subNode): bool {
            if (!$subNode instanceof FuncCall) {
                return \false;
            }
            // need pull Scope from target traversed sub Node
            return !$this->shouldSkip($subNode);
        });
        if (!$expr instanceof FuncCall) {
            return null;
        }
        $variable = new Variable('arrayIsListFunction');
        $function = $this->createClosure();
        $expression = new Expression(new Assign($variable, $function));
        $expr->name = $variable;
        return [$expression, $node];
    }
    private function createClosure(): Closure
    {
        if ($this->cachedClosure instanceof Closure) {
            return clone $this->cachedClosure;
        }
        $stmts = $this->inlineCodeParser->parseFile(__DIR__ . '/../../snippet/array_is_list_closure.php.inc');
        /** @var Expression $expression */
        $expression = $stmts[0];
        $expr = $expression->expr;
        if (!$expr instanceof Closure) {
            throw new ShouldNotHappenException();
        }
        $this->cachedClosure = $expr;
        return $expr;
    }
    private function shouldSkip(CallLike $callLike): bool
    {
        if (!$callLike instanceof FuncCall) {
            return \false;
        }
        if (!$this->isName($callLike, 'array_is_list')) {
            return \true;
        }
        $scope = $callLike->getAttribute(AttributeKey::SCOPE);
        if ($scope instanceof Scope && $scope->isInFunctionExists('array_is_list')) {
            return \true;
        }
        $args = $callLike->getArgs();
        return count($args) !== 1;
    }
}
