<?php

declare (strict_types=1);
namespace Rector\DowngradePhp82\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\Ternary;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @changelog https://www.php.net/manual/en/migration82.other-changes.php#migration82.other-changes.functions.spl
 *
 * @see \Rector\Tests\DowngradePhp82\Rector\FuncCall\DowngradeIteratorCountToArrayRector\DowngradeIteratorCountToArrayRectorTest
 * @see https://3v4l.org/TPW7a#v8.1.31
 */
final class DowngradeIteratorCountToArrayRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(ArgsAnalyzer $argsAnalyzer, BetterNodeFinder $betterNodeFinder)
    {
        $this->argsAnalyzer = $argsAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Ternary::class, FuncCall::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Ensure pass Traversable instance before use in iterator_count() and iterator_to_array()', [new CodeSample(<<<'CODE_SAMPLE'
function test(array|Traversable $data) {
    $c = iterator_count($data);
    $v = iterator_to_array($data);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function test(array|Traversable $data) {
    $c = iterator_count(is_array($data) ? new ArrayIterator($data) : $data);
    $v = iterator_to_array(is_array($data) ? new ArrayIterator($data) : $data);
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Ternary|FuncCall $node
     * @return null|FuncCall|NodeVisitor::DONT_TRAVERSE_CHILDREN
     */
    public function refactor(Node $node)
    {
        if ($node instanceof Ternary) {
            $hasIsArrayCheck = (bool) $this->betterNodeFinder->findFirst($node, function (Node $subNode): bool {
                return $subNode instanceof FuncCall && $this->isName($subNode, 'is_array');
            });
            if ($hasIsArrayCheck) {
                return NodeVisitor::DONT_TRAVERSE_CHILDREN;
            }
            return null;
        }
        if (!$this->isNames($node, ['iterator_count', 'iterator_to_array'])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $arg = $node->getArg('iterator', 0);
        if (!$arg instanceof Arg) {
            return null;
        }
        $type = $this->nodeTypeResolver->getType($arg->value);
        if ($this->shouldSkip($type)) {
            return null;
        }
        $position = $this->argsAnalyzer->resolveArgPosition($node->getArgs(), 'iterator', 0);
        Assert::isInstanceOf($node->args[$position], Arg::class);
        $firstValue = $node->args[$position]->value;
        $node->args[$position]->value = new Ternary($this->nodeFactory->createFuncCall('is_array', [new Arg($firstValue)]), new New_(new FullyQualified('ArrayIterator'), [new Arg($firstValue)]), $firstValue);
        return $node;
    }
    private function shouldSkip(Type $type): bool
    {
        if ($type->isArray()->yes()) {
            return \false;
        }
        $type = TypeCombinator::removeNull($type);
        return $type->isObject()->yes();
    }
}
