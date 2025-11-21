<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpParser\Node;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\AssignOp;
use Argtyper202511\PhpParser\Node\Expr\AssignOp\BitwiseAnd as AssignBitwiseAnd;
use Argtyper202511\PhpParser\Node\Expr\AssignOp\BitwiseOr as AssignBitwiseOr;
use Argtyper202511\PhpParser\Node\Expr\AssignOp\BitwiseXor as AssignBitwiseXor;
use Argtyper202511\PhpParser\Node\Expr\AssignOp\Concat as AssignConcat;
use Argtyper202511\PhpParser\Node\Expr\AssignOp\Div as AssignDiv;
use Argtyper202511\PhpParser\Node\Expr\AssignOp\Minus as AssignMinus;
use Argtyper202511\PhpParser\Node\Expr\AssignOp\Mod as AssignMod;
use Argtyper202511\PhpParser\Node\Expr\AssignOp\Mul as AssignMul;
use Argtyper202511\PhpParser\Node\Expr\AssignOp\Plus as AssignPlus;
use Argtyper202511\PhpParser\Node\Expr\AssignOp\Pow as AssignPow;
use Argtyper202511\PhpParser\Node\Expr\AssignOp\ShiftLeft as AssignShiftLeft;
use Argtyper202511\PhpParser\Node\Expr\AssignOp\ShiftRight as AssignShiftRight;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BitwiseAnd;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BitwiseXor;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Concat;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Div;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Equal;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Greater;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Minus;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Mod;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Mul;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotEqual;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Plus;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Pow;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\ShiftLeft;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\ShiftRight;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Smaller;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\Cast\Bool_;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
final class AssignAndBinaryMap
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var array<class-string<BinaryOp>, class-string<BinaryOp>>
     */
    private const BINARY_OP_TO_INVERSE_CLASSES = [Identical::class => NotIdentical::class, NotIdentical::class => Identical::class, Equal::class => NotEqual::class, NotEqual::class => Equal::class, Greater::class => SmallerOrEqual::class, Smaller::class => GreaterOrEqual::class, GreaterOrEqual::class => Smaller::class, SmallerOrEqual::class => Greater::class];
    /**
     * @var array<class-string<AssignOp>, class-string<BinaryOp>>
     */
    private const ASSIGN_OP_TO_BINARY_OP_CLASSES = [AssignBitwiseOr::class => BitwiseOr::class, AssignBitwiseAnd::class => BitwiseAnd::class, AssignBitwiseXor::class => BitwiseXor::class, AssignPlus::class => Plus::class, AssignDiv::class => Div::class, AssignMul::class => Mul::class, AssignMinus::class => Minus::class, AssignConcat::class => Concat::class, AssignPow::class => Pow::class, AssignMod::class => Mod::class, AssignShiftLeft::class => ShiftLeft::class, AssignShiftRight::class => ShiftRight::class];
    /**
     * @var array<class-string<BinaryOp>, class-string<AssignOp>>
     */
    private $binaryOpToAssignClasses = [];
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        /** @var array<class-string<BinaryOp>, class-string<AssignOp>> $binaryClassesToAssignOp */
        $binaryClassesToAssignOp = array_flip(self::ASSIGN_OP_TO_BINARY_OP_CLASSES);
        $this->binaryOpToAssignClasses = $binaryClassesToAssignOp;
    }
    /**
     * @return class-string<BinaryOp|AssignOp>|null
     */
    public function getAlternative(Node $node): ?string
    {
        $nodeClass = get_class($node);
        if ($node instanceof AssignOp) {
            return self::ASSIGN_OP_TO_BINARY_OP_CLASSES[$nodeClass] ?? null;
        }
        if ($node instanceof BinaryOp) {
            return $this->binaryOpToAssignClasses[$nodeClass] ?? null;
        }
        return null;
    }
    /**
     * @return class-string<BinaryOp>|null
     */
    public function getInversed(BinaryOp $binaryOp): ?string
    {
        $nodeClass = get_class($binaryOp);
        return self::BINARY_OP_TO_INVERSE_CLASSES[$nodeClass] ?? null;
    }
    public function getTruthyExpr(Expr $expr): Expr
    {
        if ($expr instanceof Bool_) {
            return $expr;
        }
        if ($expr instanceof BooleanNot) {
            return $expr;
        }
        $exprType = $this->nodeTypeResolver->getType($expr);
        // $type = $scope->getType($expr);
        if ($exprType->isBoolean()->yes()) {
            return $expr;
        }
        return new Bool_($expr);
    }
}
