<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\CodeQuality\Rector\Closure;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/blog/new-in-symfony-5-3-config-builder-classes
 * @deprecated as feature deprecated in Symfony 7.4 https://github.com/symfony/symfony/pull/62135
 */
final class StringExtensionToConfigBuilderRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert PHP fluent configs to Symfony 5.3 builder classes with method API', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('security', [
        'firewalls' => [
            'dev' => [
                'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                'security' => false,
            ],
        ],
    ]);
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $securityConfig): void {
    $securityConfig->firewall('dev')
        ->pattern('^/(_(profiler|wdt)|css|images|js)/')
        ->security(false);
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        throw new ShouldNotHappenException(\sprintf('The "%s" rector is deprecated as Symfony deprecated the feature in 7.4. See https://github.com/symfony/symfony/pull/62135', self::class));
    }
}
