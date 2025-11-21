<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp80\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PHPStan\Reflection\FunctionReflection;
use Argtyper202511\PHPStan\Reflection\MethodReflection;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\Rector\DowngradePhp80\NodeAnalyzer\UnnamedArgumentResolver;
use Argtyper202511\Rector\NodeAnalyzer\ArgsAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\DowngradeNamedArgumentRectorTest
 */
final class DowngradeNamedArgumentRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\DowngradePhp80\NodeAnalyzer\UnnamedArgumentResolver
     */
    private $unnamedArgumentResolver;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(ReflectionResolver $reflectionResolver, UnnamedArgumentResolver $unnamedArgumentResolver, ArgsAnalyzer $argsAnalyzer)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->unnamedArgumentResolver = $unnamedArgumentResolver;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class, New_::class, FuncCall::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove named argument', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->execute(b: 100);
    }

    private function execute($a = null, $b = null)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->execute(null, 100);
    }

    private function execute($a = null, $b = null)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param MethodCall|StaticCall|New_|FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        $args = $node->getArgs();
        if (!$this->argsAnalyzer->hasNamedArg($args)) {
            return null;
        }
        return $this->removeNamedArguments($node, $args);
    }
    /**
     * @param Arg[] $args
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\FuncCall $node
     */
    private function removeNamedArguments($node, array $args) : ?Node
    {
        if ($node instanceof New_) {
            $functionLikeReflection = $this->reflectionResolver->resolveMethodReflectionFromNew($node);
        } else {
            $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        }
        if (!$functionLikeReflection instanceof MethodReflection && !$functionLikeReflection instanceof FunctionReflection) {
            // remove leftovers in case of unknown type, to avoid crashing on unknown syntax
            if ($node instanceof MethodCall) {
                $callerType = $this->getType($node->var);
                if ($callerType instanceof MixedType) {
                    foreach ($node->getArgs() as $arg) {
                        if ($arg->name instanceof Node) {
                            $arg->name = null;
                        }
                    }
                    return $node;
                }
            }
            return null;
        }
        $node->args = $this->unnamedArgumentResolver->resolveFromReflection($functionLikeReflection, $args);
        return $node;
    }
}
