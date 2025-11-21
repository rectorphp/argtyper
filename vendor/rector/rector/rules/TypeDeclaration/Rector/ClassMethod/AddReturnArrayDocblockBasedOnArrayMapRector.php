<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\FunctionLike;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\IntersectionType;
use Argtyper202511\PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnArrayDocblockBasedOnArrayMapRector\AddReturnArrayDocblockBasedOnArrayMapRectorTest
 */
final class AddReturnArrayDocblockBasedOnArrayMapRector extends AbstractRector
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
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
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
    public function __construct(BetterNodeFinder $betterNodeFinder, ReturnAnalyzer $returnAnalyzer, StaticTypeMapper $staticTypeMapper, TypeFactory $typeFactory, PhpDocTypeChanger $phpDocTypeChanger, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->returnAnalyzer = $returnAnalyzer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeFactory = $typeFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @return array docblock based on array_map() return strict type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function getItems(array $items)
    {
        return array_map(function ($item): int {
            return $item->id;
        }, $items);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return int[]
     */
    public function getItems(array $items)
    {
        return array_map(function ($item): int {
            return $item->id;
        }, $items);
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     * @return null|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\ClassMethod
     */
    public function refactor(Node $node)
    {
        $returnsScoped = $this->betterNodeFinder->findReturnsScoped($node);
        if ($this->hasNonArrayReturnType($node)) {
            return null;
        }
        // nothing to return? skip it
        if ($returnsScoped === []) {
            return null;
        }
        // only returns with expr and no void
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($node, $returnsScoped)) {
            return null;
        }
        $closureReturnTypes = [];
        foreach ($returnsScoped as $returnScoped) {
            if (!$returnScoped->expr instanceof FuncCall) {
                return null;
            }
            $arrayMapClosure = $this->matchArrayMapClosure($returnScoped->expr);
            if (!$arrayMapClosure instanceof FunctionLike) {
                return null;
            }
            if (!$arrayMapClosure->returnType instanceof Node) {
                return null;
            }
            $closureReturnTypes[] = $this->staticTypeMapper->mapPhpParserNodePHPStanType($arrayMapClosure->returnType);
        }
        $returnType = $this->typeFactory->createMixedPassedOrUnionType($closureReturnTypes);
        $arrayType = new ArrayType(new MixedType(), $returnType);
        $functionLikePhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $returnOriginalType = $functionLikePhpDocInfo->getReturnType();
        if ($returnOriginalType instanceof ArrayType && !$returnOriginalType->getItemType() instanceof MixedType) {
            return null;
        }
        if ($returnOriginalType instanceof IntersectionType) {
            return null;
        }
        $hasChanged = $this->phpDocTypeChanger->changeReturnType($node, $functionLikePhpDocInfo, $arrayType);
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function hasNonArrayReturnType($functionLike): bool
    {
        if (!$functionLike->returnType instanceof Identifier) {
            return \false;
        }
        return $functionLike->returnType->toLowerString() !== 'array';
    }
    /**
     * @return \PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction|null
     */
    private function matchArrayMapClosure(FuncCall $funcCall)
    {
        if (!$this->isName($funcCall, 'array_map')) {
            return null;
        }
        if ($funcCall->isFirstClassCallable()) {
            return null;
        }
        // lets infer strict array_map() type
        $firstArg = $funcCall->getArgs()[0];
        if (!$firstArg->value instanceof Closure && !$firstArg->value instanceof ArrowFunction) {
            return null;
        }
        return $firstArg->value;
    }
}
