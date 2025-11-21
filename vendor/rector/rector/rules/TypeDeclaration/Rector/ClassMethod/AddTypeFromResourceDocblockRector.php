<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @deprecated as this rule does not solve any real problem and breaks code. The manual work is still needed.
 */
final class AddTypeFromResourceDocblockRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add param and return types on resource docblock', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param resource|null $resource
     */
    public function setResource($resource)
    {
    }

    /**
     * @return resource|null
     */
    public function getResource()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function setResource(?App\ValueObject\Resource $resource)
    {
    }

    public function getResource(): ?App\ValueObject\Resource
    {
    }
}
CODE_SAMPLE
, ['Argtyper202511\\App\\ValueObject\\Resource'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULLABLE_TYPE;
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        throw new ShouldNotHappenException(\sprintf('"%s" rule is deprecated as it does not solve any real problem and breaks code. The manual work is still needed', self::class));
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::stringNotEmpty(\current($configuration));
    }
}
