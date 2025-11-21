<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Scalar\DNumber;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\FloatType;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnDocblockForScalarArrayFromAssignsRector\AddReturnDocblockForScalarArrayFromAssignsRectorTest
 */
final class AddReturnDocblockForScalarArrayFromAssignsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer
     */
    private $returnAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(BetterNodeFinder $betterNodeFinder, ReturnAnalyzer $returnAnalyzer, PhpDocTypeChanger $phpDocTypeChanger, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->returnAnalyzer = $returnAnalyzer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @return docblock for scalar array from strict array assignments', [new CodeSample(<<<'CODE_SAMPLE'
function getSomeItems()
{
    $items = [];
    $items[] = 'hey';
    $items[] = 'hello';
    return $items;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/**
 * @return string[]
 */
function getSomeItems()
{
    $items = [];
    $items[] = 'hey';
    $items[] = 'hello';
    return $items;
}
CODE_SAMPLE
), new CodeSample(<<<'CODE_SAMPLE'
function getNumbers(): array
{
    $numbers = [];
    $numbers[] = 1;
    $numbers[] = 2;
    return $numbers;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/**
 * @return int[]
 */
function getNumbers(): array
{
    $numbers = [];
    $numbers[] = 1;
    $numbers[] = 2;
    return $numbers;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $returnType = $phpDocInfo->getReturnType();
        if (!$returnType instanceof MixedType || $returnType->isExplicitMixed()) {
            return null;
        }
        if ($node->returnType instanceof Node && !$this->isName($node->returnType, 'array')) {
            return null;
        }
        $returnsScoped = $this->betterNodeFinder->findReturnsScoped($node);
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($node, $returnsScoped)) {
            return null;
        }
        $returnedVariableNames = $this->extractReturnedVariableNames($returnsScoped);
        if ($returnedVariableNames === []) {
            return null;
        }
        $scalarArrayTypes = [];
        foreach ($returnedVariableNames as $returnedVariableName) {
            $scalarType = $this->resolveScalarArrayTypeForVariable($node, $returnedVariableName);
            if ($scalarType instanceof Type) {
                $scalarArrayTypes[] = $scalarType;
            } else {
                return null;
            }
        }
        $firstScalarType = $scalarArrayTypes[0];
        foreach ($scalarArrayTypes as $scalarArrayType) {
            if (!$firstScalarType->equals($scalarArrayType)) {
                return null;
            }
        }
        $arrayType = new ArrayType(new MixedType(), $firstScalarType);
        $hasChanged = $this->phpDocTypeChanger->changeReturnType($node, $phpDocInfo, $arrayType);
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param Return_[] $returnsScoped
     * @return string[]
     */
    private function extractReturnedVariableNames(array $returnsScoped): array
    {
        $variableNames = [];
        foreach ($returnsScoped as $returnScoped) {
            if (!$returnScoped->expr instanceof Variable) {
                continue;
            }
            $variableName = $this->getName($returnScoped->expr);
            if ($variableName !== null) {
                $variableNames[] = $variableName;
            }
        }
        return array_unique($variableNames);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    private function resolveScalarArrayTypeForVariable($node, string $variableName): ?Type
    {
        $assigns = $this->betterNodeFinder->findInstancesOfScoped([$node], Assign::class);
        $scalarTypes = [];
        $arrayHasInitialized = \false;
        $arrayHasDimAssigns = \false;
        foreach ($assigns as $assign) {
            if ($assign->var instanceof Variable && $this->isName($assign->var, $variableName) && ($assign->expr instanceof Array_ && $assign->expr->items === [])) {
                $arrayHasInitialized = \true;
                continue;
            }
            if (!$assign->var instanceof ArrayDimFetch) {
                continue;
            }
            /** @var ArrayDimFetch $arrayDimFetch */
            $arrayDimFetch = $assign->var;
            if (!$arrayDimFetch->var instanceof Variable) {
                continue;
            }
            if (!$this->isName($arrayDimFetch->var, $variableName)) {
                continue;
            }
            if ($arrayDimFetch->dim !== null) {
                continue;
            }
            $arrayHasDimAssigns = \true;
            $scalarType = $this->resolveScalarType($assign->expr);
            if ($scalarType instanceof Type) {
                $scalarTypes[] = $scalarType;
            } else {
                return null;
            }
        }
        if (!$arrayHasInitialized || !$arrayHasDimAssigns) {
            return null;
        }
        if ($scalarTypes === []) {
            return null;
        }
        $firstType = $scalarTypes[0];
        foreach ($scalarTypes as $scalarType) {
            if (!$firstType->equals($scalarType)) {
                return null;
            }
        }
        return $firstType;
    }
    private function resolveScalarType(Expr $expr): ?Type
    {
        if ($expr instanceof String_) {
            return new StringType();
        }
        if ($expr instanceof Int_) {
            return new IntegerType();
        }
        if ($expr instanceof DNumber) {
            return new FloatType();
        }
        $exprType = $this->nodeTypeResolver->getNativeType($expr);
        if ($exprType->isScalar()->yes()) {
            return $exprType;
        }
        return null;
    }
}
