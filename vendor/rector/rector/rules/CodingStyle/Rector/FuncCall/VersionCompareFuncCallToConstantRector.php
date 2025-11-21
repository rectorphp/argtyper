<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Greater;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Smaller;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\Util\PhpVersionFactory;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector\VersionCompareFuncCallToConstantRectorTest
 */
final class VersionCompareFuncCallToConstantRector extends AbstractRector
{
    /**
     * @var array<string, class-string<BinaryOp>>
     */
    private const OPERATOR_TO_COMPARISON = ['=' => Identical::class, '==' => Identical::class, 'eq' => Identical::class, '!=' => NotIdentical::class, '<>' => NotIdentical::class, 'ne' => NotIdentical::class, '>' => Greater::class, 'gt' => Greater::class, '<' => Smaller::class, 'lt' => Smaller::class, '>=' => GreaterOrEqual::class, 'ge' => GreaterOrEqual::class, '<=' => SmallerOrEqual::class, 'le' => SmallerOrEqual::class];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes use of call to version compare function to use of PHP version constant', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        version_compare(PHP_VERSION, '5.3.0', '<');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        PHP_VERSION_ID < 50300;
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
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node, 'version_compare')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (count($node->getArgs()) !== 3) {
            return null;
        }
        $args = $node->getArgs();
        if (!$this->isPhpVersionConstant($args[0]->value) && !$this->isPhpVersionConstant($args[1]->value)) {
            return null;
        }
        $left = $this->getNewNodeForArg($args[0]->value);
        $right = $this->getNewNodeForArg($args[1]->value);
        if (!$left instanceof Expr) {
            return null;
        }
        if (!$right instanceof Expr) {
            return null;
        }
        /** @var String_ $operator */
        $operator = $args[2]->value;
        $comparisonClass = self::OPERATOR_TO_COMPARISON[$operator->value];
        return new $comparisonClass($left, $right);
    }
    private function isPhpVersionConstant(Expr $expr): bool
    {
        if (!$expr instanceof ConstFetch) {
            return \false;
        }
        return $expr->name->toString() === 'PHP_VERSION';
    }
    /**
     * @return \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Scalar\Int_|null
     */
    private function getNewNodeForArg(Expr $expr)
    {
        if ($this->isPhpVersionConstant($expr)) {
            return new ConstFetch(new Name('PHP_VERSION_ID'));
        }
        return $this->getVersionNumberFormVersionString($expr);
    }
    private function getVersionNumberFormVersionString(Expr $expr): ?Int_
    {
        if (!$expr instanceof String_) {
            return null;
        }
        $value = PhpVersionFactory::createIntVersion($expr->value);
        return new Int_($value);
    }
}
