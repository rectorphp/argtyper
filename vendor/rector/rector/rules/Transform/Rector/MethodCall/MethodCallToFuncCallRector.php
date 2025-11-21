<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Transform\ValueObject\MethodCallToFuncCall;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @note used extensively https://github.com/search?q=MethodCallToFuncCallRector%3A%3Aclass&type=code
 * @see \Rector\Tests\Transform\Rector\MethodCall\MethodCallToFuncCallRector\MethodCallToFuncCallRectorTest
 */
final class MethodCallToFuncCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var MethodCallToFuncCall[]
     */
    private $methodCallsToFuncCalls = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change method call to function call', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function show()
    {
        return $this->render('some_template');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function show()
    {
        return view('some_template');
    }
}
CODE_SAMPLE
, [new MethodCallToFuncCall('SomeClass', 'render', 'view')])]);
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
        if ($node->isFirstClassCallable()) {
            return null;
        }
        foreach ($this->methodCallsToFuncCalls as $methodCallToFuncCall) {
            if (!$this->isName($node->name, $methodCallToFuncCall->getMethodName())) {
                continue;
            }
            if (!$this->isObjectType($node->var, new ObjectType($methodCallToFuncCall->getObjectType()))) {
                continue;
            }
            return new FuncCall(new FullyQualified($methodCallToFuncCall->getFunctionName()), $node->getArgs());
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsInstanceOf($configuration, MethodCallToFuncCall::class);
        $this->methodCallsToFuncCalls = $configuration;
    }
}
