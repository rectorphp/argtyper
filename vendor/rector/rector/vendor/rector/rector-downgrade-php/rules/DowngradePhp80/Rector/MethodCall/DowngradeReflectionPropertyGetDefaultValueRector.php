<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp80\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Coalesce;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionPropertyGetDefaultValueRector\DowngradeReflectionPropertyGetDefaultValueRectorTest
 */
final class DowngradeReflectionPropertyGetDefaultValueRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Downgrade ReflectionProperty->getDefaultValue()', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionProperty $reflectionProperty)
    {
        return $reflectionProperty->getDefaultValue();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionProperty $reflectionProperty)
    {
        return $reflectionProperty->getDeclaringClass()->getDefaultProperties()[$reflectionProperty->getName()] ?? null;
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
        if (!$this->isName($node->name, 'getDefaultValue')) {
            return null;
        }
        $objectType = $this->nodeTypeResolver->getType($node->var);
        if (!$objectType instanceof ObjectType) {
            return null;
        }
        if ($objectType->getClassName() !== 'ReflectionProperty') {
            return null;
        }
        $getName = new MethodCall($node->var, 'getName');
        $getDeclaringClassMethodCall = new MethodCall($node->var, 'getDeclaringClass');
        $getDefaultPropertiesMethodCall = new MethodCall($getDeclaringClassMethodCall, 'getDefaultProperties');
        $arrayDimFetch = new ArrayDimFetch($getDefaultPropertiesMethodCall, $getName);
        return new Coalesce($arrayDimFetch, $this->nodeFactory->createNull());
    }
}
