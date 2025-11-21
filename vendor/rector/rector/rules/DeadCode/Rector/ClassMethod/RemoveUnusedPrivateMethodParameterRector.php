<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\DeadCode\NodeCollector\UnusedParameterResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector\RemoveUnusedPrivateMethodParameterRectorTest
 */
final class RemoveUnusedPrivateMethodParameterRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeCollector\UnusedParameterResolver
     */
    private $unusedParameterResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(UnusedParameterResolver $unusedParameterResolver, PhpDocTagRemover $phpDocTagRemover, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory, BetterNodeFinder $betterNodeFinder)
    {
        $this->unusedParameterResolver = $unusedParameterResolver;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused parameter, if not required by interface or parent class', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private function run($value, $value2)
    {
         $this->value = $value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    private function run($value)
    {
         $this->value = $value;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPrivate()) {
                continue;
            }
            $unusedParameters = $this->unusedParameterResolver->resolve($classMethod);
            if ($unusedParameters === []) {
                continue;
            }
            // early remove callers
            if (!$this->removeCallerArgs($node, $classMethod, $unusedParameters)) {
                continue;
            }
            $unusedParameterPositions = \array_keys($unusedParameters);
            foreach (\array_keys($classMethod->params) as $key) {
                if (!\in_array($key, $unusedParameterPositions, \true)) {
                    continue;
                }
                unset($classMethod->params[$key]);
            }
            // reset param keys
            $classMethod->params = \array_values($classMethod->params);
            $this->clearPhpDocInfo($classMethod, $unusedParameters);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param Param[] $unusedParameters
     */
    private function removeCallerArgs(Class_ $class, ClassMethod $classMethod, array $unusedParameters) : bool
    {
        $classMethods = $class->getMethods();
        if ($classMethods === []) {
            return \false;
        }
        $methodName = $this->getName($classMethod);
        $keysArg = \array_keys($unusedParameters);
        $classObjectType = new ObjectType((string) $this->getName($class));
        $callers = [];
        foreach ($classMethods as $classMethod) {
            /** @var MethodCall[]|StaticCall[] $callers */
            $callers = \array_merge($callers, $this->resolveCallers($classMethod, $methodName, $classObjectType));
        }
        foreach ($callers as $caller) {
            if ($caller->isFirstClassCallable()) {
                return \false;
            }
            foreach ($caller->getArgs() as $key => $arg) {
                if ($arg->unpack) {
                    return \false;
                }
                if ($arg->name instanceof Identifier) {
                    if (isset($unusedParameters[$key]) && $this->isName($unusedParameters[$key], (string) $this->getName($arg->name))) {
                        continue;
                    }
                    return \false;
                }
            }
        }
        foreach ($callers as $caller) {
            $this->cleanupArgs($caller, $keysArg);
        }
        return \true;
    }
    /**
     * @param int[] $keysArg
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    private function cleanupArgs($call, array $keysArg) : void
    {
        $args = $call->getArgs();
        foreach (\array_keys($args) as $key) {
            if (\in_array($key, $keysArg, \true)) {
                unset($args[$key]);
            }
        }
        // reset arg keys
        $call->args = \array_values($args);
    }
    /**
     * @return MethodCall[]|StaticCall[]
     */
    private function resolveCallers(ClassMethod $classMethod, string $methodName, ObjectType $classObjectType) : array
    {
        return $this->betterNodeFinder->find($classMethod, function (Node $subNode) use($methodName, $classObjectType) : bool {
            if (!$subNode instanceof MethodCall && !$subNode instanceof StaticCall) {
                return \false;
            }
            $nodeToCheck = $subNode instanceof MethodCall ? $subNode->var : $subNode->class;
            if (!$this->isObjectType($nodeToCheck, $classObjectType)) {
                return \false;
            }
            return $this->isName($subNode->name, $methodName);
        });
    }
    /**
     * @param Param[] $unusedParameters
     */
    private function clearPhpDocInfo(ClassMethod $classMethod, array $unusedParameters) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $hasChanged = \false;
        foreach ($unusedParameters as $unusedParameter) {
            $parameterName = $this->getName($unusedParameter->var);
            if ($parameterName === null) {
                continue;
            }
            $paramTagValueNode = $phpDocInfo->getParamTagValueByName($parameterName);
            if (!$paramTagValueNode instanceof ParamTagValueNode) {
                continue;
            }
            if ($paramTagValueNode->parameterName !== '$' . $parameterName) {
                continue;
            }
            $hasTagRemoved = $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $paramTagValueNode);
            if ($hasTagRemoved) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
        }
    }
}
