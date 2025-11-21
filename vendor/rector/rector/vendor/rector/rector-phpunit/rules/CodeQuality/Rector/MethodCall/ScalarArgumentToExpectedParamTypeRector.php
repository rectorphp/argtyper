<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Scalar;
use Argtyper202511\PhpParser\Node\Scalar\Float_;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Rector\PHPUnit\CodeQuality\Reflection\MethodParametersAndReturnTypesResolver;
use Rector\PHPUnit\Enum\BehatClassName;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\ScalarArgumentToExpectedParamTypeRector\ScalarArgumentToExpectedParamTypeRectorTest
 */
final class ScalarArgumentToExpectedParamTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PHPUnit\CodeQuality\Reflection\MethodParametersAndReturnTypesResolver
     */
    private $methodParametersAndReturnTypesResolver;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, MethodParametersAndReturnTypesResolver $methodParametersAndReturnTypesResolver, ReflectionResolver $reflectionResolver)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->methodParametersAndReturnTypesResolver = $methodParametersAndReturnTypesResolver;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Correct expected type in setter of tests, if param type is strictly defined', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    public function test()
    {
        $someClass = new SomeClass();
        $someClass->setPhone(12345);
    }
}

final class SomeClass
{
    public function setPhone(string $phone)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    public function test()
    {
        $someClass = new SomeClass();
        $someClass->setPhone('12345');
    }
}

final class SomeClass
{
    public function setPhone(string $phone)
    {
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
        return [MethodCall::class, StaticCall::class, New_::class];
    }
    /**
     * @param MethodCall|StaticCall|New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipCall($node)) {
            return null;
        }
        $hasChanged = \false;
        $callParameterTypes = $this->methodParametersAndReturnTypesResolver->resolveCallParameterTypes($node);
        $callParameterNames = $this->methodParametersAndReturnTypesResolver->resolveCallParameterNames($node);
        foreach ($node->getArgs() as $key => $arg) {
            if (!$arg->value instanceof Scalar) {
                continue;
            }
            $knownParameterType = $callParameterTypes[$key] ?? null;
            if ($arg->name instanceof Identifier) {
                $argName = $arg->name->toString();
                foreach ($callParameterNames as $keyParameterNames => $callParameterName) {
                    if ($argName === $callParameterName) {
                        $knownParameterType = $callParameterTypes[$keyParameterNames] ?? null;
                        break;
                    }
                }
            }
            if (!$knownParameterType instanceof Type) {
                continue;
            }
            // remove null
            $knownParameterType = TypeCombinator::removeNull($knownParameterType);
            if ($knownParameterType instanceof StringType) {
                if ($arg->value instanceof Int_) {
                    $arg->value = new String_((string) $arg->value->value);
                    $hasChanged = \true;
                }
                if ($arg->value instanceof Float_) {
                    $arg->value = new String_((string) $arg->value->value);
                    $hasChanged = \true;
                }
            }
            if ($knownParameterType instanceof IntegerType && $arg->value instanceof String_) {
                $arg->value = new Int_((int) $arg->value->value);
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\New_ $callLike
     */
    private function shouldSkipCall($callLike): bool
    {
        if (!$this->isInTestClass($callLike)) {
            return \true;
        }
        if ($callLike->isFirstClassCallable()) {
            return \true;
        }
        if ($callLike->getArgs() === []) {
            return \true;
        }
        return !$this->hasStringOrNumberArguments($callLike);
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\New_ $callLike
     */
    private function hasStringOrNumberArguments($callLike): bool
    {
        foreach ($callLike->getArgs() as $arg) {
            if ($arg->value instanceof Int_) {
                return \true;
            }
            if ($arg->value instanceof String_) {
                return \true;
            }
            if ($arg->value instanceof Float_) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\New_ $call
     */
    private function isInTestClass($call): bool
    {
        $callerClassReflection = $this->reflectionResolver->resolveClassReflection($call);
        if (!$callerClassReflection instanceof ClassReflection) {
            return $this->testsNodeAnalyzer->isInTestClass($call);
        }
        if ($callerClassReflection->is(BehatClassName::CONTEXT)) {
            return \true;
        }
        return $this->testsNodeAnalyzer->isInTestClass($call);
    }
}
