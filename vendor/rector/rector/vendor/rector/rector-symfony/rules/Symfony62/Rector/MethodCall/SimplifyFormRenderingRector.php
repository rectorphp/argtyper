<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Symfony62\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony62\Rector\MethodCall\SimplifyFormRenderingRector\SimplifyFormRenderingRectorTest
 */
final class SimplifyFormRenderingRector extends AbstractRector
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
        return new RuleDefinition('Symplify form rendering by not calling `->createView()` on `render` function', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReplaceFormCreateViewFunctionCall extends AbstractController
{
    public function form(): Response
    {
        return $this->render('form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReplaceFormCreateViewFunctionCall extends AbstractController
{
    public function form(): Response
    {
        return $this->render('form.html.twig', [
            'form' => $form,
        ]);
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->controllerAnalyzer->isController($node->var)) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node->name, 'render')) {
            return null;
        }
        if (!isset($node->args[1])) {
            return null;
        }
        /** @var Arg $arg */
        $arg = $node->args[1];
        if (!$arg->value instanceof Array_) {
            return null;
        }
        $methodCallOrNull = $this->processRemoveCreateView($arg->value->items);
        if ($methodCallOrNull === null) {
            return null;
        }
        $arg->value->items = $methodCallOrNull;
        return $node;
    }
    /**
     * @param ArrayItem[]$arrayItems
     *
     * @return array<(ArrayItem)>|null
     */
    private function processRemoveCreateView(array $arrayItems): ?array
    {
        $replaced = \false;
        foreach ($arrayItems as $arrayItem) {
            if (!$arrayItem->value instanceof MethodCall) {
                continue;
            }
            if (!$this->isName($arrayItem->value->name, 'createView')) {
                continue;
            }
            if (!$this->isObjectType($arrayItem->value->var, new ObjectType('Argtyper202511\Symfony\Component\Form\FormInterface'))) {
                continue;
            }
            $replaced = \true;
            $arrayItem->value = $arrayItem->value->var;
        }
        if (!$replaced) {
            return null;
        }
        return $arrayItems;
    }
}
