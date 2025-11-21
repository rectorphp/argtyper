<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PHPStan\Type\Constant\ConstantArrayType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\NodeFinder\ReturnNodeFinder;
use Argtyper202511\Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Argtyper202511\Rector\TypeDeclarationDocblocks\TypeResolver\ConstantArrayTypeGeneralizer;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\DocblockReturnArrayFromDirectArrayInstanceRector\DocblockReturnArrayFromDirectArrayInstanceRectorTest
 */
final class DocblockReturnArrayFromDirectArrayInstanceRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\TypeDeclarationDocblocks\TypeResolver\ConstantArrayTypeGeneralizer
     */
    private $constantArrayTypeGeneralizer;
    /**
     * @readonly
     * @var \Rector\TypeDeclarationDocblocks\NodeFinder\ReturnNodeFinder
     */
    private $returnNodeFinder;
    /**
     * @readonly
     * @var \Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer
     */
    private $usefulArrayTagNodeAnalyzer;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpDocTypeChanger $phpDocTypeChanger, ConstantArrayTypeGeneralizer $constantArrayTypeGeneralizer, ReturnNodeFinder $returnNodeFinder, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->constantArrayTypeGeneralizer = $constantArrayTypeGeneralizer;
        $this->returnNodeFinder = $returnNodeFinder;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
    }
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add simple @return array docblock based on direct single level direct return of []', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function getItems(): array
    {
        return [
            'hey' => 'now',
        ];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return array<string, string>
     */
    public function getItems(): array
    {
        return [
            'hey' => 'now',
        ];
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($phpDocInfo->getReturnTagValue())) {
            return null;
        }
        $soleReturn = $this->returnNodeFinder->findOnlyReturnWithExpr($node);
        if (!$soleReturn instanceof Return_) {
            return null;
        }
        if (!$soleReturn->expr instanceof Array_) {
            return null;
        }
        // resolve simple type
        $returnedType = $this->getType($soleReturn->expr);
        if (!$returnedType instanceof ConstantArrayType) {
            return null;
        }
        if ($returnedType->getReferencedClasses() !== []) {
            // better handled by shared-interface/class rule, to avoid turning objects to mixed
            return null;
        }
        $genericTypeNode = $this->constantArrayTypeGeneralizer->generalize($returnedType);
        $this->phpDocTypeChanger->changeReturnTypeNode($node, $phpDocInfo, $genericTypeNode);
        return $node;
    }
}
