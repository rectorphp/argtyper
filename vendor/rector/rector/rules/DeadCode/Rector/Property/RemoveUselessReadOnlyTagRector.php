<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\Property;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Property\RemoveUselessReadOnlyTagRector\RemoveUselessReadOnlyTagRectorTest
 */
final class RemoveUselessReadOnlyTagRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    public function __construct(VisibilityManipulator $visibilityManipulator, PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater)
    {
        $this->visibilityManipulator = $visibilityManipulator;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove useless `@readonly` annotation on native readonly type', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @readonly
     */
    private readonly string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private readonly string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class, Property::class, Param::class];
    }
    /**
     * @param Class_|Property|Param $node
     */
    public function refactor(Node $node) : ?Node
    {
        // for param, only on property promotion
        if ($node instanceof Param && !$node->isPromoted()) {
            return null;
        }
        if (!$this->visibilityManipulator->isReadonly($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $readonlyDoc = $phpDocInfo->getByName('readonly');
        if (!$readonlyDoc instanceof PhpDocTagNode) {
            return null;
        }
        if (!$readonlyDoc->value instanceof GenericTagValueNode) {
            return null;
        }
        if ($readonlyDoc->value->value !== '') {
            return null;
        }
        $phpDocInfo->removeByName('readonly');
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::READONLY_PROPERTY;
    }
}
