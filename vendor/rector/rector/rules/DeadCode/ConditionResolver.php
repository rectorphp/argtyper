<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode;

use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Equal;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotEqual;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\Rector\DeadCode\Contract\ConditionInterface;
use Argtyper202511\Rector\DeadCode\ValueObject\BinaryToVersionCompareCondition;
use Argtyper202511\Rector\DeadCode\ValueObject\VersionCompareCondition;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Util\PhpVersionFactory;
final class ConditionResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, PhpVersionProvider $phpVersionProvider, ValueResolver $valueResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->valueResolver = $valueResolver;
    }
    public function resolveFromExpr(Expr $expr) : ?ConditionInterface
    {
        if ($this->isVersionCompareFuncCall($expr)) {
            /** @var FuncCall $expr */
            return $this->resolveVersionCompareConditionForFuncCall($expr);
        }
        if (!$expr instanceof Identical && !$expr instanceof Equal && !$expr instanceof NotIdentical && !$expr instanceof NotEqual) {
            return null;
        }
        $binaryClass = \get_class($expr);
        if ($this->isVersionCompareFuncCall($expr->left)) {
            /** @var FuncCall $funcCall */
            $funcCall = $expr->left;
            return $this->resolveFuncCall($funcCall, $expr->right, $binaryClass);
        }
        if ($this->isVersionCompareFuncCall($expr->right)) {
            /** @var FuncCall $funcCall */
            $funcCall = $expr->right;
            $versionCompareCondition = $this->resolveVersionCompareConditionForFuncCall($funcCall);
            if (!$versionCompareCondition instanceof VersionCompareCondition) {
                return null;
            }
            $expectedValue = $this->valueResolver->getValue($expr->left);
            return new BinaryToVersionCompareCondition($versionCompareCondition, $binaryClass, $expectedValue);
        }
        return null;
    }
    private function isVersionCompareFuncCall(Expr $expr) : bool
    {
        if (!$expr instanceof FuncCall) {
            return \false;
        }
        return $this->nodeNameResolver->isName($expr, 'version_compare');
    }
    private function resolveVersionCompareConditionForFuncCall(FuncCall $funcCall) : ?VersionCompareCondition
    {
        $firstVersion = $this->resolveArgumentValue($funcCall, 0);
        if ($firstVersion === null) {
            return null;
        }
        $secondVersion = $this->resolveArgumentValue($funcCall, 1);
        if ($secondVersion === null) {
            return null;
        }
        // includes compare sign as 3rd argument
        $versionCompareSign = null;
        if (isset($funcCall->args[2]) && $funcCall->args[2] instanceof Arg) {
            $versionCompareSign = $this->valueResolver->getValue($funcCall->args[2]->value);
        }
        return new VersionCompareCondition($firstVersion, $secondVersion, $versionCompareSign);
    }
    private function resolveFuncCall(FuncCall $funcCall, Expr $expr, string $binaryClass) : ?BinaryToVersionCompareCondition
    {
        $versionCompareCondition = $this->resolveVersionCompareConditionForFuncCall($funcCall);
        if (!$versionCompareCondition instanceof VersionCompareCondition) {
            return null;
        }
        $expectedValue = $this->valueResolver->getValue($expr);
        return new BinaryToVersionCompareCondition($versionCompareCondition, $binaryClass, $expectedValue);
    }
    private function resolveArgumentValue(FuncCall $funcCall, int $argumentPosition) : ?int
    {
        if (!isset($funcCall->args[$argumentPosition])) {
            return null;
        }
        if (!$funcCall->args[$argumentPosition] instanceof Arg) {
            return null;
        }
        $firstArgValue = $funcCall->args[$argumentPosition]->value;
        /** @var mixed|null $version */
        $version = $this->valueResolver->getValue($firstArgValue);
        if (\in_array($version, ['PHP_VERSION', 'PHP_VERSION_ID'], \true)) {
            return $this->phpVersionProvider->provide();
        }
        if (\is_string($version)) {
            return PhpVersionFactory::createIntVersion($version);
        }
        return $version;
    }
}
