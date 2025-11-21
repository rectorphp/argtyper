<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\Rector\If_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\If_\NullableCompareToNullRector\NullableCompareToNullRectorTest
 */
final class NullableCompareToNullRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes negate of empty comparison of nullable value to explicit === or !== compare', [new CodeSample(<<<'CODE_SAMPLE'
/** @var stdClass|null $value */
if ($value) {
}

if (!$value) {
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/** @var stdClass|null $value */
if ($value !== null) {
}

if ($value === null) {
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->cond instanceof BooleanNot && $this->isNullableNonScalarType($node->cond->expr)) {
            $node->cond = new Identical($node->cond->expr, $this->nodeFactory->createNull());
            return $node;
        }
        if ($this->isNullableNonScalarType($node->cond)) {
            $node->cond = new NotIdentical($node->cond, $this->nodeFactory->createNull());
            return $node;
        }
        return null;
    }
    private function isNullableNonScalarType(Expr $expr) : bool
    {
        $nativeType = $this->nodeTypeResolver->getNativeType($expr);
        // is non-nullable?
        if (!TypeCombinator::containsNull($nativeType)) {
            return \false;
        }
        if (!$nativeType instanceof UnionType) {
            return \false;
        }
        // is array?
        foreach ($nativeType->getTypes() as $subType) {
            if ($subType->isArray()->yes()) {
                return \false;
            }
        }
        $nativeType = TypeCombinator::removeNull($nativeType);
        return !$nativeType->isScalar()->yes();
    }
}
