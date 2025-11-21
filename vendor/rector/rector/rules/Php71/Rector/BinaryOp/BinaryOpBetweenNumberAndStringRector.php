<?php

declare (strict_types=1);
namespace Rector\Php71\Rector\BinaryOp;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Coalesce;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Concat;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Scalar;
use Argtyper202511\PhpParser\Node\Scalar\Float_;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\MagicConst\Line;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\Type\Constant\ConstantStringType;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\BinaryOpBetweenNumberAndStringRectorTest
 */
final class BinaryOpBetweenNumberAndStringRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(ExprAnalyzer $exprAnalyzer)
    {
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::BINARY_OP_NUMBER_STRING;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change binary operation between some number + string to PHP 7.1 compatible version', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5 + '';
        $value = 5.0 + 'hi';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5 + 0;
        $value = 5.0 + 0.0;
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
        return [BinaryOp::class];
    }
    /**
     * @param BinaryOp $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Concat) {
            return null;
        }
        if ($node instanceof Coalesce) {
            return null;
        }
        if ($this->exprAnalyzer->isNonTypedFromParam($node->left)) {
            return null;
        }
        if ($this->exprAnalyzer->isNonTypedFromParam($node->right)) {
            return null;
        }
        if ($this->isStringOrStaticNonNumericString($node->left) && $this->nodeTypeResolver->isNumberType($node->right)) {
            $node->left = $this->nodeTypeResolver->getNativeType($node->right)->isInteger()->yes() ? new Int_(0) : new Float_(0);
            return $node;
        }
        if ($this->isStringOrStaticNonNumericString($node->right) && $this->nodeTypeResolver->isNumberType($node->left)) {
            $node->right = $this->nodeTypeResolver->getNativeType($node->left)->isInteger()->yes() ? new Int_(0) : new Float_(0);
            return $node;
        }
        return null;
    }
    private function isStringOrStaticNonNumericString(Expr $expr): bool
    {
        // replace only scalar values, not variables/constants/etc.
        if (!$expr instanceof Scalar && !$expr instanceof Variable) {
            return \false;
        }
        if ($expr instanceof Line) {
            return \false;
        }
        if ($expr instanceof String_) {
            return !is_numeric($expr->value);
        }
        $exprStaticType = $this->getType($expr);
        if ($exprStaticType instanceof ConstantStringType) {
            return !is_numeric($exprStaticType->getValue());
        }
        return \false;
    }
}
