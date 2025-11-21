<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Renaming\Rector\FunctionLike;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\CallLike;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Renaming\ValueObject\RenameFunctionLikeParamWithinCallLikeArg;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as very narrow use case and suited better for custom rule.
 */
final class RenameFunctionLikeParamWithinCallLikeArgRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Rename param within closures and arrow functions based on use with specified method calls', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
(new SomeClass)->process(function ($param) {});
CODE_SAMPLE
, <<<'CODE_SAMPLE'
(new SomeClass)->process(function ($parameter) {});
CODE_SAMPLE
, [new RenameFunctionLikeParamWithinCallLikeArg('SomeClass', 'process', 0, 0, 'parameter')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param CallLike $node
     */
    public function refactor(Node $node) : ?Node
    {
        throw new ShouldNotHappenException(\sprintf('"%s" rule is deprecated as too specific and not practical for general core rules', self::class));
    }
    /**
     * @param RenameFunctionLikeParamWithinCallLikeArg[] $configuration
     */
    public function configure(array $configuration) : void
    {
    }
}
