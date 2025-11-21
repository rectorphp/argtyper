<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\BinaryOp;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyClass;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\BinaryOp\RequestIsMainRector\RequestIsMainRectorTest
 */
final class RequestIsMainRector extends AbstractRector
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns status code numbers to constants', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;

class SomeController
{
    public function index(Request $request): bool
    {
        return $request->getRequestType() === HttpKernel::MASTER_REQUEST;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;

class SomeController
{
    public function index(Request $request): bool
    {
        return $request->isMasterRequestType();
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
        return [BinaryOp::class];
    }
    /**
     * @param BinaryOp $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->left instanceof MethodCall) {
            return null;
        }
        $methodCall = $node->left;
        if (!$this->isRequestGetRequestType($methodCall)) {
            return null;
        }
        if (!$this->isHttpKernelMainRequestClassConstFetch($node->right)) {
            return null;
        }
        $requestClassReflection = $this->reflectionProvider->getClass(SymfonyClass::REQUEST);
        $methodName = $requestClassReflection->hasMethod('isMainRequest') ? 'isMainRequest' : 'isMasterRequest';
        return new MethodCall($methodCall->var, $methodName);
    }
    private function isRequestGetRequestType(MethodCall $methodCall): bool
    {
        if (!$this->isName($methodCall->name, 'getRequestType')) {
            return \false;
        }
        return $this->isObjectType($methodCall->var, new ObjectType(SymfonyClass::REQUEST));
    }
    private function isHttpKernelMainRequestClassConstFetch(Expr $expr): bool
    {
        if (!$expr instanceof ClassConstFetch) {
            return \false;
        }
        if (!$this->isNames($expr->class, [SymfonyClass::HTTP_KERNEL_INTERFACE, SymfonyClass::HTTP_KERNEL])) {
            return \false;
        }
        return $this->isNames($expr->name, ['MASTER_REQUEST', 'MAIN_REQUEST']);
    }
}
