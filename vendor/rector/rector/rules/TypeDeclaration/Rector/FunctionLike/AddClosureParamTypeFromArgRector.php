<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\FunctionLike;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddClosureParamTypeFromArg;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @deprecated as too specific and not useful in Rector core. Implement it locally instead if needed.
 */
final class AddClosureParamTypeFromArgRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add closure param type based on known passed service/string types of method calls', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$app = new Container();
$app->extend(SomeClass::class, function ($parameter) {});
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$app = new Container();
$app->extend(SomeClass::class, function (SomeClass $parameter) {});
CODE_SAMPLE
, [new AddClosureParamTypeFromArg('Container', 'extend', 1, 0)])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated as too specific and not practical for general core rules', self::class));
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, AddClosureParamTypeFromArg::class);
    }
}
