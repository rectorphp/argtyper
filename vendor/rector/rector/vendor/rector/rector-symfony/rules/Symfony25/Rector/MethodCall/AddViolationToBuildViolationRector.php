<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Symfony25\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/questions/25264922/symfony-2-5-addviolationat-deprecated-use-buildviolation
 *
 * @see \Rector\Symfony\Tests\Symfony25\Rector\MethodCall\AddViolationToBuildViolationRector\AddViolationToBuildViolationRectorTest
 */
final class AddViolationToBuildViolationRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change `$context->addViolationAt` to `$context->buildViolation` on Validator ExecutionContext', [new CodeSample(<<<'CODE_SAMPLE'
$context->addViolationAt('property', 'The value {{ value }} is invalid.', array(
    '{{ value }}' => $invalidValue,
));
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$context->buildViolation('The value {{ value }} is invalid.')
    ->atPath('property')
    ->setParameter('{{ value }}', $invalidValue)
    ->addViolation();
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
    public function refactor(Node $node): ?MethodCall
    {
        $objectType = $this->nodeTypeResolver->getType($node->var);
        if (!$objectType instanceof ObjectType) {
            return null;
        }
        $executionContext = new ObjectType('Argtyper202511\Symfony\Component\Validator\Context\ExecutionContextInterface');
        if (!$executionContext->isSuperTypeOf($objectType)->yes()) {
            return null;
        }
        if (!$this->isName($node->name, 'addViolationAt')) {
            return null;
        }
        $args = $node->getArgs();
        $path = $args[0];
        $message = $args[1];
        $node->name = new Identifier('buildViolation');
        $node->args = [$message];
        $node = new MethodCall($node, 'atPath', [$path]);
        $node = $this->buildFluentWithParameters($node, $args);
        $node = $this->buildFluentWithInvalidValue($node, $args);
        $node = $this->buildFluentWithPlural($node, $args);
        $node = $this->buildFluentWithCode($node, $args);
        $node = new MethodCall($node, 'addViolation');
        return $node;
    }
    /**
     * @param Arg[] $args
     */
    private function buildFluentWithParameters(MethodCall $methodCall, array $args): MethodCall
    {
        if (isset($args[2]) && $args[2]->value instanceof Array_) {
            foreach ($args[2]->value->items as $item) {
                if ($item instanceof ArrayItem && $item->key instanceof Expr) {
                    $methodCall = new MethodCall($methodCall, 'setParameter', [new Arg($item->key), new Arg($item->value)]);
                }
            }
        }
        return $methodCall;
    }
    /**
     * @param Arg[] $args
     */
    private function buildFluentWithInvalidValue(MethodCall $methodCall, array $args): MethodCall
    {
        if (isset($args[3])) {
            $methodCall = new MethodCall($methodCall, 'setInvalidValue', [new Arg($args[3]->value)]);
        }
        return $methodCall;
    }
    /**
     * @param Arg[] $args
     */
    private function buildFluentWithPlural(MethodCall $methodCall, array $args): MethodCall
    {
        if (isset($args[4])) {
            $methodCall = new MethodCall($methodCall, 'setPlural', [new Arg($args[4]->value)]);
        }
        return $methodCall;
    }
    /**
     * @param Arg[] $args
     */
    private function buildFluentWithCode(MethodCall $methodCall, array $args): MethodCall
    {
        if (isset($args[5])) {
            $methodCall = new MethodCall($methodCall, 'setCode', [new Arg($args[5]->value)]);
        }
        return $methodCall;
    }
}
