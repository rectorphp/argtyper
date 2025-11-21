<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php83\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\Rector\Php82\NodeManipulator\ReadonlyClassManipulator;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php83\Rector\Class_\ReadOnlyAnonymousClassRector\ReadOnlyAnonymousClassRectorTest
 */
final class ReadOnlyAnonymousClassRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php82\NodeManipulator\ReadonlyClassManipulator
     */
    private $readonlyClassManipulator;
    public function __construct(ReadonlyClassManipulator $readonlyClassManipulator)
    {
        $this->readonlyClassManipulator = $readonlyClassManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Decorate read-only anonymous class with `readonly` attribute', [new CodeSample(<<<'CODE_SAMPLE'
new class
{
    public function __construct(
        private readonly string $name
    ) {
    }
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
new readonly class
{
    public function __construct(
        private string $name
    ) {
    }
};
CODE_SAMPLE
)]);
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
        if (!$node->isAnonymous()) {
            return null;
        }
        return $this->readonlyClassManipulator->process($node);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::READONLY_ANONYMOUS_CLASS;
    }
}
