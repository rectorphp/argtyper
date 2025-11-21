<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\NullsafeMethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class CallAnalyzer
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var array<class-string<Expr>>
     */
    private const OBJECT_CALL_TYPES = [MethodCall::class, NullsafeMethodCall::class, StaticCall::class];
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function isObjectCall(Expr $expr): bool
    {
        if ($expr instanceof BooleanNot) {
            $expr = $expr->expr;
        }
        if ($expr instanceof BinaryOp) {
            $isObjectCallLeft = $this->isObjectCall($expr->left);
            $isObjectCallRight = $this->isObjectCall($expr->right);
            return $isObjectCallLeft || $isObjectCallRight;
        }
        foreach (self::OBJECT_CALL_TYPES as $objectCallType) {
            if ($expr instanceof $objectCallType) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param If_[] $ifs
     */
    public function doesIfHasObjectCall(array $ifs): bool
    {
        foreach ($ifs as $if) {
            if ($this->isObjectCall($if->cond)) {
                return \true;
            }
        }
        return \false;
    }
    public function isNewInstance(Variable $variable): bool
    {
        $scope = $variable->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        $type = $scope->getNativeType($variable);
        if (!$type instanceof ObjectType) {
            return \false;
        }
        $className = $type->getClassName();
        if (!$this->reflectionProvider->hasClass($className)) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        return $classReflection->getNativeReflection()->isInstantiable();
    }
}
