<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Symfony60\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Symfony\Enum\SymfonyFunction;
use Argtyper202511\Rector\Symfony\ValueObject\ReplaceServiceArgument;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Symfony\Tests\Symfony60\Rector\FuncCall\ReplaceServiceArgumentRector\ReplaceServiceArgumentRectorTest
 */
final class ReplaceServiceArgumentRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var ReplaceServiceArgument[]
     */
    private $replaceServiceArguments = [];
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace defined service() argument in Symfony PHP config', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return service(ContainerInterface::class);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return service('service_container');
CODE_SAMPLE
, [new ReplaceServiceArgument('ContainerInterface', new String_('service_container'))])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?FuncCall
    {
        if (!$this->isName($node->name, SymfonyFunction::SERVICE)) {
            return null;
        }
        $firstArg = $node->args[0];
        if (!$firstArg instanceof Arg) {
            return null;
        }
        foreach ($this->replaceServiceArguments as $replaceServiceArgument) {
            if (!$this->valueResolver->isValue($firstArg->value, $replaceServiceArgument->getOldValue())) {
                continue;
            }
            $node->args[0] = new Arg($replaceServiceArgument->getNewValueExpr());
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, ReplaceServiceArgument::class);
        $this->replaceServiceArguments = $configuration;
    }
}
