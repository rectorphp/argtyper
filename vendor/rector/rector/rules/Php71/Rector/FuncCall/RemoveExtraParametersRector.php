<?php

declare (strict_types=1);
namespace Rector\Php71\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PHPStan\Reflection\FunctionReflection;
use Argtyper202511\PHPStan\Reflection\MethodReflection;
use Argtyper202511\PHPStan\Reflection\Php\PhpMethodReflection;
use Argtyper202511\PHPStan\Reflection\Type\UnionTypeMethodReflection;
use Rector\Enum\ObjectReference;
use Rector\NodeAnalyzer\VariadicAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\RemoveExtraParametersRectorTest
 */
final class RemoveExtraParametersRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\VariadicAnalyzer
     */
    private $variadicAnalyzer;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(VariadicAnalyzer $variadicAnalyzer, ReflectionResolver $reflectionResolver)
    {
        $this->variadicAnalyzer = $variadicAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::NO_EXTRA_PARAMETERS;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove extra parameters', [new CodeSample('strlen("asdf", 1);', 'strlen("asdf");')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, MethodCall::class, StaticCall::class];
    }
    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        // unreliable count of arguments
        $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        if ($functionLikeReflection instanceof UnionTypeMethodReflection) {
            return null;
        }
        if ($functionLikeReflection === null) {
            return null;
        }
        if ($functionLikeReflection instanceof PhpMethodReflection) {
            if ($functionLikeReflection->isAbstract()) {
                return null;
            }
            $classReflection = $functionLikeReflection->getDeclaringClass();
            if ($classReflection->isInterface()) {
                return null;
            }
        }
        $maximumAllowedParameterCount = $this->resolveMaximumAllowedParameterCount($functionLikeReflection);
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if ($this->shouldSkipFunctionReflection($functionLikeReflection)) {
            return null;
        }
        $numberOfArguments = count($node->getRawArgs());
        if ($numberOfArguments <= $maximumAllowedParameterCount) {
            return null;
        }
        for ($i = $maximumAllowedParameterCount; $i <= $numberOfArguments; ++$i) {
            unset($node->args[$i]);
        }
        return $node;
    }
    /**
     * @param \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection $reflection
     */
    private function shouldSkipFunctionReflection($reflection): bool
    {
        if ($reflection instanceof FunctionReflection) {
            $fileName = (string) $reflection->getFileName();
            if (strpos($fileName, 'phpstan.phar') !== \false) {
                return \true;
            }
        }
        if ($reflection instanceof MethodReflection) {
            $classReflection = $reflection->getDeclaringClass();
            $fileName = (string) $classReflection->getFileName();
            if (strpos($fileName, 'phpstan.phar') !== \false) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    private function shouldSkip($call): bool
    {
        if ($call->args === []) {
            return \true;
        }
        if ($call instanceof StaticCall) {
            if (!$call->class instanceof Name) {
                return \true;
            }
            if ($this->isName($call->class, ObjectReference::PARENT)) {
                return \true;
            }
        }
        return $this->variadicAnalyzer->hasVariadicParameters($call);
    }
    /**
     * @param \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection $functionLikeReflection
     */
    private function resolveMaximumAllowedParameterCount($functionLikeReflection): int
    {
        $parameterCounts = [0];
        foreach ($functionLikeReflection->getVariants() as $parametersAcceptor) {
            $parameterCounts[] = count($parametersAcceptor->getParameters());
        }
        return max($parameterCounts);
    }
}
