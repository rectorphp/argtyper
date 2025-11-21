<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\Rector\Contract\DependencyInjection\RelatedConfigInterface;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Doctrine\CodeQuality\AttributeTransformer\YamlToAttributeTransformer;
use Argtyper202511\Rector\Doctrine\CodeQuality\EntityMappingResolver;
use Argtyper202511\Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Argtyper202511\Rector\Doctrine\Set\DoctrineSetList;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersion;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Class_\YamlToAttributeDoctrineMappingRector\YamlToAttributeDoctrineMappingRectorTest
 */
final class YamlToAttributeDoctrineMappingRector extends AbstractRector implements ConfigurableRectorInterface, RelatedConfigInterface, MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Doctrine\CodeQuality\EntityMappingResolver
     */
    private $entityMappingResolver;
    /**
     * @readonly
     * @var \Rector\Doctrine\CodeQuality\AttributeTransformer\YamlToAttributeTransformer
     */
    private $yamlToAttributeTransformer;
    /**
     * @var string[]
     */
    private $yamlMappingDirectories = [];
    public function __construct(EntityMappingResolver $entityMappingResolver, YamlToAttributeTransformer $yamlToAttributeTransformer)
    {
        $this->entityMappingResolver = $entityMappingResolver;
        $this->yamlToAttributeTransformer = $yamlToAttributeTransformer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Converts YAML Doctrine Entity mapping to particular annotation mapping. You must provide a YAML directory with mappings to this rule configuration', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeEntity
{
    private $id;

    private $name;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SomeEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string')]
    private $name;
}

CODE_SAMPLE
, [__DIR__ . '/config/yaml_mapping_directory'])]);
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Class_
    {
        if ($this->yamlMappingDirectories === []) {
            throw new ShouldNotHappenException(sprintf('First, set directories with YAML entity mappings. Use "$rectorConfig->ruleWithConfiguration(%s, %s)"', self::class, "[__DIR__ . '/config/yaml_mapping_directory']"));
        }
        $entityMapping = $this->findEntityMapping($node);
        if (!$entityMapping instanceof EntityMapping) {
            return null;
        }
        $hasChanged = $this->yamlToAttributeTransformer->transform($node, $entityMapping);
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allString($configuration);
        Assert::allFileExists($configuration);
        $this->yamlMappingDirectories = $configuration;
    }
    public static function getConfigFile(): string
    {
        return DoctrineSetList::YAML_TO_ANNOTATIONS;
    }
    public function provideMinPhpVersion(): int
    {
        // required by Doctrine nested attributes
        return PhpVersion::PHP_81;
    }
    private function findEntityMapping(Class_ $class): ?EntityMapping
    {
        $className = $this->getName($class);
        if (!is_string($className)) {
            return null;
        }
        $entityMappings = $this->entityMappingResolver->resolveFromDirectories($this->yamlMappingDirectories);
        foreach ($entityMappings as $entityMapping) {
            if ($entityMapping->getClassName() !== $className) {
                continue;
            }
            return $entityMapping;
        }
        return null;
    }
}
