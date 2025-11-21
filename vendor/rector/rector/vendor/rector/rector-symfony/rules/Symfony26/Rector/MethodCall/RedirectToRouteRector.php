<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Symfony26\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony26\Rector\MethodCall\RedirectToRouteRector\RedirectToRouteRectorTest
 */
final class RedirectToRouteRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer
     */
    private $controllerAnalyzer;
    public function __construct(ControllerAnalyzer $controllerAnalyzer)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns redirect to route to short helper method in Controller in Symfony', [new CodeSample('$this->redirect($this->generateUrl("homepage"));', '$this->redirectToRoute("homepage");')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->controllerAnalyzer->isInsideController($node)) {
            return null;
        }
        if (!$this->isName($node->name, 'redirect')) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        $firstArg = $node->args[0];
        if (!$firstArg instanceof Arg) {
            return null;
        }
        $argumentValue = $firstArg->value;
        if (!$argumentValue instanceof MethodCall) {
            return null;
        }
        if (!$this->isName($argumentValue->name, 'generateUrl')) {
            return null;
        }
        if (!$this->isName($argumentValue->var, 'this')) {
            return null;
        }
        if (!$this->isDefaultReferenceType($argumentValue)) {
            return null;
        }
        return $this->nodeFactory->createMethodCall('this', 'redirectToRoute', $this->resolveArguments($node));
    }
    private function isDefaultReferenceType(MethodCall $methodCall): bool
    {
        if (!isset($methodCall->args[2])) {
            return \true;
        }
        $refTypeArg = $methodCall->args[2];
        if (!$refTypeArg instanceof Arg) {
            return \false;
        }
        if (!$refTypeArg->value instanceof ClassConstFetch) {
            return \false;
        }
        if (!$this->isName($refTypeArg->value->class, 'Argtyper202511\Symfony\Component\Routing\Generator\UrlGeneratorInterface')) {
            return \false;
        }
        return $this->isName($refTypeArg->value->name, 'ABSOLUTE_PATH');
    }
    /**
     * @return mixed[]
     */
    private function resolveArguments(MethodCall $methodCall): array
    {
        $firstArg = $methodCall->args[0];
        if (!$firstArg instanceof Arg) {
            return [];
        }
        $generateUrlNode = $firstArg->value;
        if (!$generateUrlNode instanceof MethodCall) {
            return [];
        }
        $arguments = [];
        $arguments[] = $generateUrlNode->args[0];
        if (isset($generateUrlNode->args[1])) {
            $arguments[] = $generateUrlNode->args[1];
        }
        if (!isset($generateUrlNode->args[1]) && isset($methodCall->args[1])) {
            $arguments[] = [];
        }
        if (isset($methodCall->args[1])) {
            $arguments[] = $methodCall->args[1];
        }
        return $arguments;
    }
}
