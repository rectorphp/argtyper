<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\NullableType;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Doctrine\Enum\DoctrineClass;
use Argtyper202511\Rector\Doctrine\TypedCollections\DocBlockAnalyzer\CollectionTagValueNodeAnalyzer;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\CollectionSetterParamNativeTypeRector\CollectionSetterParamNativeTypeRectorTest
 */
final class CollectionSetterParamNativeTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\TypedCollections\DocBlockAnalyzer\CollectionTagValueNodeAnalyzer
     */
    private $collectionTagValueNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, CollectionTagValueNodeAnalyzer $collectionTagValueNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->collectionTagValueNodeAnalyzer = $collectionTagValueNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add native param type to a Collection setter', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    private $items;

    /**
     * @param Collection<int, string> $items
     */
    public function setItems($items): void
    {
        $this->items = $items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    private $items;

    /**
     * @param Collection<int, string> $items
     */
    public function setItems(Collection $items): void
    {
        $this->items = $items;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?ClassMethod
    {
        if ($node->isAbstract()) {
            return null;
        }
        $isInTests = $this->testsNodeAnalyzer->isInTestClass($node);
        $hasChanged = \false;
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$classMethodPhpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        if ($classMethodPhpDocInfo->getParamTagValueNodes() === []) {
            return null;
        }
        foreach ($node->params as $param) {
            if ($param->type instanceof Node) {
                continue;
            }
            $paramTagValueNode = $classMethodPhpDocInfo->getParamTagValueByName($this->getName($param));
            if (!$paramTagValueNode instanceof ParamTagValueNode) {
                continue;
            }
            if (!$this->collectionTagValueNodeAnalyzer->detect($paramTagValueNode)) {
                continue;
            }
            $hasChanged = \true;
            $param->type = new FullyQualified(DoctrineClass::COLLECTION);
            // fix reprint position of type
            $param->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            // make nullable only 1st param, as others might require a null
            if ($param->default instanceof Expr) {
                if ($isInTests === \false) {
                    // remove default param, as no longer needed; empty collection should be passed instead
                    $param->default = null;
                } else {
                    // make type explicitly nullable
                    $collectionFullyQualified = new FullyQualified(DoctrineClass::COLLECTION);
                    $param->type = new NullableType($collectionFullyQualified);
                    $param->default = new ConstFetch(new Name('null'));
                }
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
