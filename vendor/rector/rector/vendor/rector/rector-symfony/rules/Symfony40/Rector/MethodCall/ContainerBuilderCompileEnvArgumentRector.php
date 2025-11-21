<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Symfony40\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony40\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector\ContainerBuilderCompileEnvArgumentRectorTest
 */
final class ContainerBuilderCompileEnvArgumentRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns old default value to parameter in ContainerBuilder->build() method in DI in Symfony', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\ContainerBuilder;

$containerBuilder = new ContainerBuilder();
$containerBuilder->compile();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\ContainerBuilder;

$containerBuilder = new ContainerBuilder();
$containerBuilder->compile(true);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node->name, 'compile')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('Argtyper202511\\Symfony\\Component\\DependencyInjection\\ContainerBuilder'))) {
            return null;
        }
        if (\count($node->args) === 1) {
            return null;
        }
        $node->args = $this->nodeFactory->createArgs([$this->nodeFactory->createTrue()]);
        return $node;
    }
}
