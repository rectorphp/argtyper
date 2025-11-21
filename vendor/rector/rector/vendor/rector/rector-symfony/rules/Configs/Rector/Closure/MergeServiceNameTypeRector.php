<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Configs\Rector\Closure;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Symfony\Enum\SymfonyClass;
use Argtyper202511\Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Configs\Rector\Closure\MergeServiceNameTypeRector\MergeServiceNameTypeRectorTest
 */
final class MergeServiceNameTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector
     */
    private $symfonyPhpClosureDetector;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function __construct(SymfonyPhpClosureDetector $symfonyPhpClosureDetector, ValueResolver $valueResolver)
    {
        $this->symfonyPhpClosureDetector = $symfonyPhpClosureDetector;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Merge name === type service registration, $services->set(SomeType::class, SomeType::class)', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(\App\SomeClass::class, \App\SomeClass::class);
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(\App\SomeClass::class);
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->hasChanged = \false;
        if (!$this->symfonyPhpClosureDetector->detect($node)) {
            return null;
        }
        $this->handleSetServices($node);
        if ($this->hasChanged) {
            return $node;
        }
        return null;
    }
    private function handleSetServices(Closure $closure): void
    {
        $this->traverseNodesWithCallable($closure->stmts, function (Node $node): ?MethodCall {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isSetServices($node)) {
                return null;
            }
            // must be exactly 2 args
            if (count($node->args) !== 2) {
                return null;
            }
            // exchange type and service name
            $firstArg = $node->getArgs()[0];
            $secondArg = $node->getArgs()[1];
            /** @var string $serviceName */
            $serviceName = $this->valueResolver->getValue($firstArg->value);
            $serviceType = $this->valueResolver->getValue($secondArg->value);
            if (!is_string($serviceType)) {
                return null;
            }
            if ($serviceName !== $serviceType) {
                return null;
            }
            // remove 2nd arg
            unset($node->args[1]);
            $this->hasChanged = \true;
            return $node;
        });
    }
    private function isSetServices(MethodCall $methodCall): bool
    {
        if (!$this->isName($methodCall->name, 'set')) {
            return \false;
        }
        return $this->isObjectType($methodCall->var, new ObjectType(SymfonyClass::SERVICES_CONFIGURATOR));
    }
}
