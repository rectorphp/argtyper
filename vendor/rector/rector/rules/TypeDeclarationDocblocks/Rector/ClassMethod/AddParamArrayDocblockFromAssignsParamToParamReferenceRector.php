<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
use Argtyper202511\Rector\TypeDeclarationDocblocks\NodeFinder\ArrayDimFetchFinder;
use Argtyper202511\Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromAssignsParamToParamReferenceRector\AddParamArrayDocblockFromAssignsParamToParamReferenceRectorTest
 */
final class AddParamArrayDocblockFromAssignsParamToParamReferenceRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\TypeDeclarationDocblocks\NodeFinder\ArrayDimFetchFinder
     */
    private $arrayDimFetchFinder;
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
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ArrayDimFetchFinder $arrayDimFetchFinder, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->arrayDimFetchFinder = $arrayDimFetchFinder;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
        $this->nodeDocblockTypeDecorator = $nodeDocblockTypeDecorator;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @param docblock array type, based on type to assigned parameter reference', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(array &$names): void
    {
        $names[] = 'John';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @param string[] $names
     */
    public function run(array &$names): void
    {
        $names[] = 'John';
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = \false;
        if ($node->getParams() === []) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        foreach ($node->getParams() as $param) {
            if (!$param->byRef) {
                continue;
            }
            if (!$param->type instanceof Identifier) {
                continue;
            }
            if (!$this->isName($param->type, 'array')) {
                continue;
            }
            $paramName = $this->getName($param);
            $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);
            // already defined, lets skip it
            if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($paramTagValueNode)) {
                continue;
            }
            $exprs = $this->arrayDimFetchFinder->findDimFetchAssignToVariableName($node, $paramName);
            // to kick off with one
            if (count($exprs) !== 1) {
                continue;
            }
            $assignedExprType = $this->getType($exprs[0]);
            $iterableType = new ArrayType(new MixedType(), $assignedExprType);
            $hasParamTypeChanged = $this->nodeDocblockTypeDecorator->decorateGenericIterableParamType($iterableType, $phpDocInfo, $node, $param, $paramName);
            if (!$hasParamTypeChanged) {
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
