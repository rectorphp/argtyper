<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
use Rector\TypeDeclarationDocblocks\NodeFinder\GetterClassMethodPropertyFinder;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\DocblockGetterReturnArrayFromPropertyDocblockVarRector\DocblockGetterReturnArrayFromPropertyDocblockVarRectorTest
 */
final class DocblockGetterReturnArrayFromPropertyDocblockVarRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer
     */
    private $usefulArrayTagNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator
     */
    private $nodeDocblockTypeDecorator;
    /**
     * @readonly
     * @var \Rector\TypeDeclarationDocblocks\NodeFinder\GetterClassMethodPropertyFinder
     */
    private $getterClassMethodPropertyFinder;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator, GetterClassMethodPropertyFinder $getterClassMethodPropertyFinder)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
        $this->nodeDocblockTypeDecorator = $nodeDocblockTypeDecorator;
        $this->getterClassMethodPropertyFinder = $getterClassMethodPropertyFinder;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @return array docblock to a getter method based on @var of the property', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private array $items;

    public function getItems(): array
    {
        return $this->items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private array $items;

    /**
     * @return int[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isAnonymous()) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->returnType instanceof Node) {
                continue;
            }
            if (!$this->isName($classMethod->returnType, 'array')) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($phpDocInfo->getReturnTagValue())) {
                continue;
            }
            $propertyOrParam = $this->getterClassMethodPropertyFinder->find($classMethod, $node);
            if (!$propertyOrParam instanceof Node) {
                continue;
            }
            $propertyDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($propertyOrParam);
            $varTagValueNode = $propertyDocInfo->getVarTagValueNode();
            if (!$varTagValueNode instanceof VarTagValueNode) {
                continue;
            }
            // is type useful?
            if (!$varTagValueNode->type instanceof GenericTypeNode && !$varTagValueNode->type instanceof ArrayTypeNode) {
                continue;
            }
            if (!$this->nodeDocblockTypeDecorator->decorateGenericIterableReturnType($varTagValueNode->type, $phpDocInfo, $classMethod)) {
                continue;
            }
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
