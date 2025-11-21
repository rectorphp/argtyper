<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\Catch_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\Throw_;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt\Catch_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PHPStan\Reflection\ParametersAcceptorSelector;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector\ThrowWithPreviousExceptionRectorTest
 */
final class ThrowWithPreviousExceptionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var int
     */
    private const DEFAULT_EXCEPTION_ARGUMENT_POSITION = 2;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('When throwing into a catch block, checks that the previous exception is passed to the new throw clause', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            $someCode = 1;
        } catch (Throwable $throwable) {
            throw new AnotherException('ups');
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            $someCode = 1;
        } catch (Throwable $throwable) {
            throw new AnotherException('ups', $throwable->getCode(), $throwable);
        }
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
        return [Catch_::class];
    }
    /**
     * @param Catch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $caughtThrowableVariable = $node->var;
        if (!$caughtThrowableVariable instanceof Variable) {
            return null;
        }
        $isChanged = \false;
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) use ($caughtThrowableVariable, &$isChanged): ?int {
            if (!$node instanceof Throw_) {
                return null;
            }
            $isChanged = $this->refactorThrow($node, $caughtThrowableVariable);
            return $isChanged;
        });
        if (!(bool) $isChanged) {
            return null;
        }
        return $node;
    }
    private function refactorThrow(Throw_ $throw, Variable $caughtThrowableVariable): ?int
    {
        if (!$throw->expr instanceof New_) {
            return null;
        }
        $new = $throw->expr;
        if (!$new->class instanceof Name) {
            return null;
        }
        $exceptionArgumentPosition = $this->resolveExceptionArgumentPosition($new->class);
        if ($exceptionArgumentPosition === null) {
            return null;
        }
        if ($new->isFirstClassCallable()) {
            return null;
        }
        // exception is bundled
        if (isset($new->getArgs()[$exceptionArgumentPosition])) {
            return null;
        }
        /** @var Arg|null $messageArgument */
        $messageArgument = $new->args[0] ?? null;
        $shouldUseNamedArguments = $messageArgument instanceof Arg && $messageArgument->name instanceof Identifier;
        if (!isset($new->args[0])) {
            // get previous message
            $getMessageMethodCall = new MethodCall($caughtThrowableVariable, 'getMessage');
            $new->args[0] = new Arg($getMessageMethodCall);
        } elseif ($new->args[0] instanceof Arg && $new->args[0]->name instanceof Identifier && $new->args[0]->name->toString() === 'previous' && $this->nodeComparator->areNodesEqual($new->args[0]->value, $caughtThrowableVariable)) {
            $new->args[0]->name->name = 'message';
            $new->args[0]->value = new MethodCall($caughtThrowableVariable, 'getMessage');
        }
        if (!isset($new->getArgs()[1])) {
            // get previous code
            $new->args[1] = new Arg(new MethodCall($caughtThrowableVariable, 'getCode'), \false, \false, [], $shouldUseNamedArguments ? new Identifier('code') : null);
        }
        /** @var Arg $arg1 */
        $arg1 = $new->args[1];
        if ($arg1->name instanceof Identifier && $arg1->name->toString() === 'previous') {
            $new->args[1] = new Arg(new MethodCall($caughtThrowableVariable, 'getCode'), \false, \false, [], $shouldUseNamedArguments ? new Identifier('code') : null);
            $new->args[$exceptionArgumentPosition] = $arg1;
        } else {
            $new->args[$exceptionArgumentPosition] = new Arg($caughtThrowableVariable, \false, \false, [], $shouldUseNamedArguments ? new Identifier('previous') : null);
        }
        // null the node, to fix broken format preserving printers, see https://github.com/rectorphp/rector/issues/5576
        $new->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        // nothing more to add
        return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
    }
    private function resolveExceptionArgumentPosition(Name $exceptionName): ?int
    {
        $className = $this->getName($exceptionName);
        // is native exception?
        if (strpos($className, '\\') === \false) {
            return self::DEFAULT_EXCEPTION_ARGUMENT_POSITION;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return self::DEFAULT_EXCEPTION_ARGUMENT_POSITION;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $construct = $classReflection->hasMethod(MethodName::CONSTRUCT);
        if (!$construct) {
            return self::DEFAULT_EXCEPTION_ARGUMENT_POSITION;
        }
        $extendedMethodReflection = $classReflection->getConstructor();
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($extendedMethodReflection->getVariants());
        foreach ($extendedParametersAcceptor->getParameters() as $position => $extendedParameterReflection) {
            $parameterType = $extendedParameterReflection->getType();
            $className = ClassNameFromObjectTypeResolver::resolve($extendedParameterReflection->getType());
            if ($className === null) {
                continue;
            }
            $objectType = new ObjectType('Throwable');
            if ($objectType->isSuperTypeOf($parameterType)->no()) {
                continue;
            }
            return $position;
        }
        return null;
    }
}
