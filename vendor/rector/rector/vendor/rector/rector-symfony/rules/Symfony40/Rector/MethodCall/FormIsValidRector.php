<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Symfony40\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony40\Rector\MethodCall\FormIsValidRector\FormIsValidRectorTest
 */
final class FormIsValidRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Adds `$form->isSubmitted()` validation to all `$form->isValid()` calls in Form in Symfony', [new CodeSample(<<<'CODE_SAMPLE'
if ($form->isValid()) {
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
if ($form->isSubmitted() && $form->isValid()) {
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->cond instanceof MethodCall) {
            return null;
        }
        $methodCall = $node->cond;
        if (!$methodCall->var instanceof Variable) {
            return null;
        }
        // mark child calls with known is submitted
        if ($this->isName($methodCall->name, 'isSubmitted')) {
            $this->traverseNodesWithCallable($node->stmts, static function (Node $node) {
                $node->setAttribute('has_is_submitted', \true);
                return null;
            });
            return null;
        }
        // already checked
        if ($node->getAttribute('has_is_submitted')) {
            return null;
        }
        if ($this->shouldSkipMethodCall($methodCall)) {
            return null;
        }
        /** @var string $variableName */
        $variableName = $this->getName($methodCall->var);
        $node->cond = new BooleanAnd($this->nodeFactory->createMethodCall($variableName, 'isSubmitted'), $this->nodeFactory->createMethodCall($variableName, 'isValid'));
        return $node;
    }
    private function shouldSkipMethodCall(MethodCall $methodCall): bool
    {
        if (!$this->isName($methodCall->name, 'isValid')) {
            return \true;
        }
        return !$this->isObjectType($methodCall->var, new ObjectType('Argtyper202511\Symfony\Component\Form\Form'));
    }
}
