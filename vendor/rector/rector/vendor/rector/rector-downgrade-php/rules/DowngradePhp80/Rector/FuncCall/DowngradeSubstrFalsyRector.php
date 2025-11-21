<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp80\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\AssignOp;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Concat;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\CallLike;
use Argtyper202511\PhpParser\Node\Expr\Cast;
use Argtyper202511\PhpParser\Node\Expr\Cast\String_;
use Argtyper202511\PhpParser\Node\Expr\Empty_;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Expr\Ternary;
use Argtyper202511\PhpParser\Node\Stmt\Do_;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\While_;
use Argtyper202511\PHPStan\Reflection\FunctionReflection;
use Argtyper202511\PHPStan\Reflection\MethodReflection;
use Argtyper202511\PHPStan\Type\Constant\ConstantIntegerType;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://php.watch/versions/8.0/substr-out-of-bounds
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeSubstrFalsyRector\DowngradeSubstrFalsyRectorTest
 */
final class DowngradeSubstrFalsyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var string
     */
    private const IS_FALSY_UNCASTABLE = 'is_falsy_uncastable';
    public function __construct(ReflectionResolver $reflectionResolver, ValueResolver $valueResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade substr() with cast string on possibly falsy result', [new CodeSample('substr("a", 2);', '(string) substr("a", 2);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Cast::class, Empty_::class, BooleanNot::class, Ternary::class, Identical::class, Concat::class, MethodCall::class, StaticCall::class, New_::class, AssignOp::class, If_::class, While_::class, Do_::class, ArrayItem::class, ArrayDimFetch::class, BinaryOp::class, FuncCall::class];
    }
    /**
     * @param Cast|Empty_|BooleanNot|Ternary|Identical|Concat|MethodCall|StaticCall|New_|AssignOp|If_|While_|Do_|ArrayItem|ArrayDimFetch|BinaryOp|FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Cast || $node instanceof Empty_ || $node instanceof BooleanNot || $node instanceof AssignOp) {
            $node->expr->setAttribute(self::IS_FALSY_UNCASTABLE, \true);
            return null;
        }
        if ($node instanceof Ternary) {
            if (!$node->if instanceof Expr) {
                $node->cond->setAttribute(self::IS_FALSY_UNCASTABLE, \true);
            }
            return null;
        }
        if ($node instanceof Concat) {
            $node->left->setAttribute(self::IS_FALSY_UNCASTABLE, \true);
            $node->right->setAttribute(self::IS_FALSY_UNCASTABLE, \true);
            return null;
        }
        if ($node instanceof Identical) {
            if ($this->valueResolver->isFalse($node->left)) {
                $node->right->setAttribute(self::IS_FALSY_UNCASTABLE, \true);
            }
            if ($this->valueResolver->isFalse($node->right)) {
                $node->left->setAttribute(self::IS_FALSY_UNCASTABLE, \true);
            }
            return null;
        }
        if ($node instanceof If_ || $node instanceof While_ || $node instanceof Do_) {
            $node->cond->setAttribute(self::IS_FALSY_UNCASTABLE, \true);
            return null;
        }
        if ($node instanceof ArrayItem) {
            if ($node->key instanceof Expr) {
                $node->key->setAttribute(self::IS_FALSY_UNCASTABLE, \true);
            }
            return null;
        }
        if ($node instanceof ArrayDimFetch) {
            if ($node->dim instanceof Expr) {
                $node->dim->setAttribute(self::IS_FALSY_UNCASTABLE, \true);
            }
            return null;
        }
        if ($node instanceof BinaryOp) {
            $node->left->setAttribute(self::IS_FALSY_UNCASTABLE, \true);
            $node->right->setAttribute(self::IS_FALSY_UNCASTABLE, \true);
            return null;
        }
        if ($node instanceof CallLike) {
            if ($node->isFirstClassCallable()) {
                return null;
            }
            $reflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
            if (!$reflection instanceof MethodReflection && !$reflection instanceof FunctionReflection) {
                return null;
            }
            $parameterAcceptor = ParametersAcceptorSelectorVariantsWrapper::select($reflection, $node, ScopeFetcher::fetch($node));
            foreach ($parameterAcceptor->getParameters() as $position => $parameterReflection) {
                if ($parameterReflection->getType()->isFalse()->no()) {
                    continue;
                }
                $arg = $node->getArg($parameterReflection->getName(), $position);
                if ($arg instanceof Arg) {
                    $arg->value->setAttribute(self::IS_FALSY_UNCASTABLE, \true);
                }
            }
        }
        if (!$this->isName($node, 'substr')) {
            return null;
        }
        if ($node->getAttribute(self::IS_FALSY_UNCASTABLE) === \true) {
            return null;
        }
        $type = $this->getType($node);
        if ($type->isNonEmptyString()->yes()) {
            return null;
        }
        $offset = $node->getArg('offset', 1);
        if ($offset instanceof Arg) {
            $offsetType = $this->getType($offset->value);
            if ($offsetType instanceof ConstantIntegerType && $offsetType->getValue() <= 0) {
                $length = $node->getArg('length', 2);
                if ($length instanceof Arg) {
                    $lengthType = $this->getType($length->value);
                    if ($lengthType instanceof ConstantIntegerType && $lengthType->getValue() >= 0) {
                        return null;
                    }
                    return new String_($node);
                }
                return null;
            }
        }
        return new String_($node);
    }
}
