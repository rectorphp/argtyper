<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Instanceof_;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Ternary;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector\DowngradeReflectionGetTypeRectorTest
 */
final class DowngradeReflectionGetTypeRector extends AbstractRector
{
    /**
     * @var string
     */
    private const SKIP_NODE = 'skip_node';
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Downgrade reflection $reflection->getType() method call', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionProperty $reflectionProperty)
    {
        if ($reflectionProperty->getType()) {
            return true;
        }

        return false;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionProperty $reflectionProperty)
    {
        if (method_exists($reflectionProperty, 'getType') ? $reflectionProperty->getType() ? null) {
            return true;
        }

        return false;
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
        return [MethodCall::class, Ternary::class, Instanceof_::class];
    }
    /**
     * @param MethodCall|Ternary|Instanceof_ $node
     */
    public function refactor(Node $node): ?\Argtyper202511\PhpParser\Node
    {
        if ($node instanceof Instanceof_) {
            $this->markSkipInstanceof($node);
            return null;
        }
        if ($node instanceof Ternary) {
            $this->markSkipTernary($node);
            return null;
        }
        if ($node->getAttribute(self::SKIP_NODE) === \true) {
            return null;
        }
        if (!$this->isName($node->name, 'getType')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('ReflectionProperty'))) {
            return null;
        }
        $args = [new Arg($node->var), new Arg(new String_('getType'))];
        return new Ternary($this->nodeFactory->createFuncCall('method_exists', $args), $node, $this->nodeFactory->createNull());
    }
    private function markSkipInstanceof(Instanceof_ $instanceof): void
    {
        if (!$this->isName($instanceof->class, 'ReflectionNamedType')) {
            return;
        }
        if (!$instanceof->expr instanceof MethodCall) {
            return;
        }
        // checked typed → safe
        $instanceof->expr->setAttribute(self::SKIP_NODE, \true);
    }
    private function markSkipTernary(Ternary $ternary): void
    {
        if (!$ternary->if instanceof Expr) {
            return;
        }
        if (!$ternary->cond instanceof FuncCall) {
            return;
        }
        if (!$this->isName($ternary->cond, 'method_exists')) {
            return;
        }
        $ternary->if->setAttribute(self::SKIP_NODE, \true);
    }
}
