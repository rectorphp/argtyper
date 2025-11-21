<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\Rector\ArrayDimFetch;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\AssignOp;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use Argtyper202511\PhpParser\Node\Expr\Isset_;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Unset_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Transform\ValueObject\ArrayDimFetchToMethodCall;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\ArrayDimFetch\ArrayDimFetchToMethodCallRector\ArrayDimFetchToMethodCallRectorTest
 */
final class ArrayDimFetchToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var ArrayDimFetchToMethodCall[]
     */
    private $arrayDimFetchToMethodCalls;
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change array dim fetch to method call', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$object['key'];
$object['key'] = 'value';
isset($object['key']);
unset($object['key']);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$object->get('key');
$object->set('key', 'value');
$object->has('key');
$object->unset('key');
CODE_SAMPLE
, [new ArrayDimFetchToMethodCall(new ObjectType('SomeClass'), 'get', 'set', 'has', 'unset')])]);
    }
    public function getNodeTypes(): array
    {
        return [AssignOp::class, Assign::class, Isset_::class, Unset_::class, ArrayDimFetch::class];
    }
    /**
     * @template TNode of ArrayDimFetch|Assign|Isset_|Unset_
     * @param TNode $node
     * @return ($node is Unset_ ? Stmt[]|int : ($node is Isset_ ? Expr|int : MethodCall|int|null))
     */
    public function refactor(Node $node)
    {
        if ($node instanceof AssignOp) {
            return NodeVisitor::DONT_TRAVERSE_CHILDREN;
        }
        if ($node instanceof Unset_) {
            return $this->handleUnset($node);
        }
        if ($node instanceof Isset_) {
            return $this->handleIsset($node);
        }
        if ($node instanceof Assign) {
            if (!$node->var instanceof ArrayDimFetch) {
                return null;
            }
            return $this->getMethodCall($node->var, 'set', $node->expr) ?? NodeVisitor::DONT_TRAVERSE_CHILDREN;
        }
        return $this->getMethodCall($node, 'get');
    }
    public function configure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, ArrayDimFetchToMethodCall::class);
        $this->arrayDimFetchToMethodCalls = $configuration;
    }
    /**
     * @return \PhpParser\Node\Expr|int|null
     */
    private function handleIsset(Isset_ $isset)
    {
        $issets = [];
        $exprs = [];
        foreach ($isset->vars as $var) {
            if ($var instanceof ArrayDimFetch) {
                $methodCall = $this->getMethodCall($var, 'exists');
                if ($methodCall instanceof MethodCall) {
                    $exprs[] = $methodCall;
                    continue;
                }
            }
            $issets[] = $var;
        }
        if ($exprs === []) {
            return NodeVisitor::DONT_TRAVERSE_CHILDREN;
        }
        if ($issets !== []) {
            $isset->vars = $issets;
            array_unshift($exprs, $isset);
        }
        return array_reduce($exprs, function (?Expr $carry, Expr $expr) {
            return $carry instanceof Expr ? new BooleanAnd($carry, $expr) : $expr;
        });
    }
    /**
     * @return Stmt[]|int
     */
    private function handleUnset(Unset_ $unset)
    {
        $unsets = [];
        $stmts = [];
        foreach ($unset->vars as $var) {
            if ($var instanceof ArrayDimFetch) {
                $methodCall = $this->getMethodCall($var, 'unset');
                if ($methodCall instanceof MethodCall) {
                    $stmts[] = new Expression($methodCall);
                    continue;
                }
            }
            $unsets[] = $var;
        }
        if ($stmts === []) {
            return NodeVisitor::DONT_TRAVERSE_CHILDREN;
        }
        if ($unsets !== []) {
            $unset->vars = $unsets;
            array_unshift($stmts, $unset);
        }
        return $stmts;
    }
    /**
     * @param 'get'|'set'|'exists'|'unset' $action
     */
    private function getMethodCall(ArrayDimFetch $arrayDimFetch, string $action, ?Expr $expr = null): ?MethodCall
    {
        if (!$arrayDimFetch->dim instanceof Node) {
            return null;
        }
        foreach ($this->arrayDimFetchToMethodCalls as $arrayDimFetchToMethodCall) {
            if (!$this->isObjectType($arrayDimFetch->var, $arrayDimFetchToMethodCall->getObjectType())) {
                continue;
            }
            switch ($action) {
                case 'get':
                    $method = $arrayDimFetchToMethodCall->getMethod();
                    break;
                case 'set':
                    $method = $arrayDimFetchToMethodCall->getSetMethod();
                    break;
                case 'exists':
                    $method = $arrayDimFetchToMethodCall->getExistsMethod();
                    break;
                case 'unset':
                    $method = $arrayDimFetchToMethodCall->getUnsetMethod();
                    break;
            }
            if ($method === null) {
                continue;
            }
            $args = [new Arg($arrayDimFetch->dim)];
            if ($expr instanceof Expr) {
                $args[] = new Arg($expr);
            }
            return new MethodCall($arrayDimFetch->var, $method, $args);
        }
        return null;
    }
}
