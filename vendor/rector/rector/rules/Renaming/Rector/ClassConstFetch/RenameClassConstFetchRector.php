<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Renaming\Rector\ClassConstFetch;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Renaming\Contract\RenameClassConstFetchInterface;
use Argtyper202511\Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Argtyper202511\Rector\Renaming\ValueObject\RenameClassConstFetch;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector\RenameClassConstFetchRectorTest
 */
final class RenameClassConstFetchRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var RenameClassConstFetchInterface[]
     */
    private $renameClassConstFetches = [];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace defined class constants in their calls', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$value = SomeClass::OLD_CONSTANT;
$value = SomeClass::OTHER_OLD_CONSTANT;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = SomeClass::NEW_CONSTANT;
$value = DifferentClass::NEW_CONSTANT;
CODE_SAMPLE
, [new RenameClassConstFetch('SomeClass', 'OLD_CONSTANT', 'NEW_CONSTANT'), new RenameClassAndConstFetch('SomeClass', 'OTHER_OLD_CONSTANT', 'DifferentClass', 'NEW_CONSTANT')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassConstFetch::class];
    }
    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node): ?ClassConstFetch
    {
        foreach ($this->renameClassConstFetches as $renameClassConstFetch) {
            if (!$this->isName($node->name, $renameClassConstFetch->getOldConstant())) {
                continue;
            }
            if (!$this->isObjectType($node->class, $renameClassConstFetch->getOldObjectType())) {
                continue;
            }
            if ($renameClassConstFetch instanceof RenameClassAndConstFetch) {
                return $this->createClassAndConstFetch($renameClassConstFetch);
            }
            $node->name = new Identifier($renameClassConstFetch->getNewConstant());
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, RenameClassConstFetchInterface::class);
        $this->renameClassConstFetches = $configuration;
    }
    private function createClassAndConstFetch(RenameClassAndConstFetch $renameClassAndConstFetch): ClassConstFetch
    {
        return new ClassConstFetch(new FullyQualified($renameClassAndConstFetch->getNewClass()), new Identifier($renameClassAndConstFetch->getNewConstant()));
    }
}
