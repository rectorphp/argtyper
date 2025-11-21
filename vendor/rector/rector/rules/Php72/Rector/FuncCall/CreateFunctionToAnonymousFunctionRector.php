<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php72\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Concat;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Scalar\InterpolatedString;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Php\ReservedKeywordAnalyzer;
use Argtyper202511\Rector\Php72\NodeFactory\AnonymousFunctionFactory;
use Argtyper202511\Rector\PhpParser\Parser\InlineCodeParser;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\CreateFunctionToAnonymousFunctionRectorTest
 */
final class CreateFunctionToAnonymousFunctionRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Parser\InlineCodeParser
     */
    private $inlineCodeParser;
    /**
     * @readonly
     * @var \Rector\Php72\NodeFactory\AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;
    /**
     * @readonly
     * @var \Rector\Php\ReservedKeywordAnalyzer
     */
    private $reservedKeywordAnalyzer;
    public function __construct(InlineCodeParser $inlineCodeParser, AnonymousFunctionFactory $anonymousFunctionFactory, ReservedKeywordAnalyzer $reservedKeywordAnalyzer)
    {
        $this->inlineCodeParser = $inlineCodeParser;
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATE_CREATE_FUNCTION;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Use anonymous functions instead of deprecated create_function()', [new CodeSample(<<<'CODE_SAMPLE'
class ClassWithCreateFunction
{
    public function run()
    {
        $callable = create_function('$matches', "return '$delimiter' . strtolower(\$matches[1]);");
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class ClassWithCreateFunction
{
    public function run()
    {
        $callable = function($matches) use ($delimiter) {
            return $delimiter . strtolower($matches[1]);
        };
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     * @return Closure|null
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node, 'create_function')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (count($node->getArgs()) < 2) {
            return null;
        }
        $firstExpr = $node->getArgs()[0]->value;
        $secondExpr = $node->getArgs()[1]->value;
        $params = $this->createParamsFromString($firstExpr);
        $stmts = $this->parseStringToBody($secondExpr);
        $refactored = $this->anonymousFunctionFactory->create($params, $stmts, null);
        foreach ($refactored->uses as $key => $use) {
            $variableName = $this->getName($use->var);
            if ($variableName === null) {
                continue;
            }
            if ($this->reservedKeywordAnalyzer->isNativeVariable($variableName)) {
                unset($refactored->uses[$key]);
            }
        }
        return $refactored;
    }
    /**
     * @return Param[]
     */
    private function createParamsFromString(Expr $expr): array
    {
        $content = $this->inlineCodeParser->stringify($expr);
        $content = '<?php $value = function(' . $content . ') {};';
        $nodes = $this->inlineCodeParser->parseString($content);
        /** @var Expression $expression */
        $expression = $nodes[0];
        /** @var Assign $assign */
        $assign = $expression->expr;
        $function = $assign->expr;
        if (!$function instanceof Closure) {
            throw new ShouldNotHappenException();
        }
        return $function->params;
    }
    /**
     * @return Stmt[]
     */
    private function parseStringToBody(Expr $expr): array
    {
        if (!$expr instanceof String_ && !$expr instanceof InterpolatedString && !$expr instanceof Concat) {
            // special case of code elsewhere
            return [$this->createEval($expr)];
        }
        $content = $this->inlineCodeParser->stringify($expr);
        return $this->inlineCodeParser->parseString($content);
    }
    private function createEval(Expr $expr): Expression
    {
        $evalFuncCall = new FuncCall(new Name('eval'), [new Arg($expr)]);
        return new Expression($evalFuncCall);
    }
}
