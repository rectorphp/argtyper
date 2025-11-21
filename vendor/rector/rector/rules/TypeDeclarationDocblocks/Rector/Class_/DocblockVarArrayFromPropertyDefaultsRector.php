<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
use Argtyper202511\Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\Class_\DocblockVarArrayFromPropertyDefaultsRector\DocblockVarArrayFromPropertyDefaultsRectorTest
 */
final class DocblockVarArrayFromPropertyDefaultsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator
     */
    private $nodeDocblockTypeDecorator;
    /**
     * @readonly
     * @var \Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer
     */
    private $usefulArrayTagNodeAnalyzer;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeDocblockTypeDecorator = $nodeDocblockTypeDecorator;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add @var array docblock to array property based on iterable default value', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private array $items = [1, 2, 3];
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private array $items = [1, 2, 3];
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if (!$property->type instanceof Identifier) {
                continue;
            }
            if (!$this->isName($property->type, 'array')) {
                continue;
            }
            if (\count($property->props) > 1) {
                continue;
            }
            $soleProperty = $property->props[0];
            if (!$soleProperty->default instanceof Array_) {
                continue;
            }
            $propertyDefaultType = $this->getType($soleProperty->default);
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            // type is already known
            if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($propertyPhpDocInfo->getVarTagValueNode())) {
                continue;
            }
            if ($this->nodeDocblockTypeDecorator->decorateGenericIterableVarType($propertyDefaultType, $propertyPhpDocInfo, $property)) {
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
