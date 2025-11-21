<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\Rector\StaticCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Transform\NodeAnalyzer\FuncCallStaticCallToMethodCallAnalyzer;
use Argtyper202511\Rector\Transform\ValueObject\StaticCallToMethodCall;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @note used in Laravel Rector https://github.com/driftingly/rector-laravel/blob/f46812350e0b4ef535c82d3759e86bb5c6abb651/config/sets/laravel-static-to-injection.php#L23
 *
 * @see \Rector\Tests\Transform\Rector\StaticCall\StaticCallToMethodCallRector\StaticCallToMethodCallRectorTest
 */
final class StaticCallToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\Transform\NodeAnalyzer\FuncCallStaticCallToMethodCallAnalyzer
     */
    private $funcCallStaticCallToMethodCallAnalyzer;
    /**
     * @var StaticCallToMethodCall[]
     */
    private $staticCallsToMethodCalls = [];
    public function __construct(FuncCallStaticCallToMethodCallAnalyzer $funcCallStaticCallToMethodCallAnalyzer)
    {
        $this->funcCallStaticCallToMethodCallAnalyzer = $funcCallStaticCallToMethodCallAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change static call to service method via constructor injection', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use Nette\Utils\FileSystem;

class SomeClass
{
    public function run()
    {
        return FileSystem::write('file', 'content');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use App\Custom\SmartFileSystem;

class SomeClass
{
    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(SmartFileSystem $smartFileSystem)
    {
        $this->smartFileSystem = $smartFileSystem;
    }

    public function run()
    {
        return $this->smartFileSystem->dumpFile('file', 'content');
    }
}
CODE_SAMPLE
, [new StaticCallToMethodCall('Argtyper202511\\Nette\\Utils\\FileSystem', 'write', 'Argtyper202511\\App\\Custom\\SmartFileSystem', 'dumpFile')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $class = $node;
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            $this->traverseNodesWithCallable($classMethod, function (Node $node) use($class, $classMethod, &$hasChanged) {
                if (!$node instanceof StaticCall) {
                    return null;
                }
                foreach ($this->staticCallsToMethodCalls as $staticCallToMethodCall) {
                    if (!$staticCallToMethodCall->isStaticCallMatch($node)) {
                        continue;
                    }
                    if ($classMethod->isStatic()) {
                        return $this->refactorToInstanceCall($node, $staticCallToMethodCall);
                    }
                    $expr = $this->funcCallStaticCallToMethodCallAnalyzer->matchTypeProvidingExpr($class, $classMethod, $staticCallToMethodCall->getClassObjectType());
                    $methodName = $this->getMethodName($node, $staticCallToMethodCall);
                    $hasChanged = \true;
                    return new MethodCall($expr, $methodName, $node->args);
                }
                return $node;
            });
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, StaticCallToMethodCall::class);
        $this->staticCallsToMethodCalls = $configuration;
    }
    private function getMethodName(StaticCall $staticCall, StaticCallToMethodCall $staticCallToMethodCall) : string
    {
        if ($staticCallToMethodCall->getMethodName() === '*') {
            $methodName = $this->getName($staticCall->name);
        } else {
            $methodName = $staticCallToMethodCall->getMethodName();
        }
        if (!\is_string($methodName)) {
            throw new ShouldNotHappenException();
        }
        return $methodName;
    }
    private function refactorToInstanceCall(StaticCall $staticCall, StaticCallToMethodCall $staticCallToMethodCall) : MethodCall
    {
        $new = new New_(new FullyQualified($staticCallToMethodCall->getClassType()));
        $methodName = $this->getMethodName($staticCall, $staticCallToMethodCall);
        return new MethodCall($new, $methodName, $staticCall->args);
    }
}
