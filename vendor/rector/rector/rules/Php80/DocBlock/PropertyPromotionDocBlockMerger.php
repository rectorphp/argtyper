<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\DocBlock;

use Argtyper202511\PhpParser\Comment;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Argtyper202511\Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
final class PropertyPromotionDocBlockMerger
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover
     */
    private $varTagRemover;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper, PhpDocTypeChanger $phpDocTypeChanger, VarTagRemover $varTagRemover, PhpDocInfoPrinter $phpDocInfoPrinter, DocBlockUpdater $docBlockUpdater)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->varTagRemover = $varTagRemover;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function mergePropertyAndParamDocBlocks(Property $property, Param $param, ?ParamTagValueNode $paramTagValueNode) : void
    {
        $paramComments = $param->getComments();
        // already has @param tag → give it priority over @var and remove @var
        if ($paramTagValueNode instanceof ParamTagValueNode) {
            $propertyDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            if ($propertyDocInfo->hasByType(VarTagValueNode::class)) {
                $propertyDocInfo->removeByType(VarTagValueNode::class);
                $propertyComments = $this->phpDocInfoPrinter->printToComments($propertyDocInfo);
                /** @var Comment[] $mergedComments */
                $mergedComments = \array_merge($paramComments, $propertyComments);
                $mergedComments = $this->removeEmptyComments($mergedComments);
                $param->setAttribute(AttributeKey::COMMENTS, $mergedComments);
            }
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($param);
    }
    public function decorateParamWithPropertyPhpDocInfo(ClassMethod $classMethod, Property $property, Param $param, string $paramName) : void
    {
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $param->setAttribute(AttributeKey::PHP_DOC_INFO, $propertyPhpDocInfo);
        // make sure the docblock is useful
        if (!$param->type instanceof Node) {
            $varTagValueNode = $propertyPhpDocInfo->getVarTagValueNode();
            if (!$varTagValueNode instanceof VarTagValueNode) {
                return;
            }
            $paramType = $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($varTagValueNode, $property);
            $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $this->phpDocTypeChanger->changeParamType($classMethod, $classMethodPhpDocInfo, $paramType, $param, $paramName);
        } else {
            $paramType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        }
        $this->varTagRemover->removeVarPhpTagValueNodeIfNotComment($param, $paramType);
    }
    /**
     * @param Comment[] $mergedComments
     * @return Comment[]
     */
    private function removeEmptyComments(array $mergedComments) : array
    {
        return \array_filter($mergedComments, static function (Comment $comment) : bool {
            return $comment->getText() !== '';
        });
    }
}
