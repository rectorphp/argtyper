<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Doctrine\TypedCollections\DocBlockAnalyzer\CollectionTagValueNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\DefaultCollectionKeyRector\DefaultCollectionKeyRectorTest
 */
final class DefaultCollectionKeyRector extends AbstractRector
{
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
    /**
     * @readonly
     * @var \Rector\Doctrine\TypedCollections\DocBlockAnalyzer\CollectionTagValueNodeAnalyzer
     */
    private $collectionTagValueNodeAnalyzer;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater, CollectionTagValueNodeAnalyzer $collectionTagValueNodeAnalyzer)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->collectionTagValueNodeAnalyzer = $collectionTagValueNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add default key to Collection generic type if missing in @param or @return of class method', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class ReturnSimpleCollection
{
    /**
     * @return Collection<string>
     */
    public function someMethod()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class ReturnSimpleCollection
{
    /**
     * @return Collection<int, string>
     */
    public function someMethod()
    {
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
        $hasChanged = \false;
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        foreach ($classMethodPhpDocInfo->getParamTagValueNodes() as $paramTagValueNode) {
            if ($this->processTagValueNode($paramTagValueNode)) {
                $hasChanged = \true;
            }
        }
        $returnTagValueNode = $classMethodPhpDocInfo->getReturnTagValue();
        if ($returnTagValueNode instanceof ReturnTagValueNode && $this->processTagValueNode($returnTagValueNode)) {
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode $tagValueNode
     */
    private function processTagValueNode($tagValueNode): bool
    {
        if (!$this->collectionTagValueNodeAnalyzer->detect($tagValueNode)) {
            return \false;
        }
        /** @var GenericTypeNode $genericTypeNode */
        $genericTypeNode = $tagValueNode->type;
        if (count($genericTypeNode->genericTypes) !== 1) {
            return \false;
        }
        $valueGenericType = $genericTypeNode->genericTypes[0];
        $genericTypeNode->genericTypes = [new IdentifierTypeNode('int'), $valueGenericType];
        return \true;
    }
}
