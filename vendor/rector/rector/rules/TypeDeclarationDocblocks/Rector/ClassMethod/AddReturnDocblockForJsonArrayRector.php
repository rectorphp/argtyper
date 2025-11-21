<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Enum\NetteClassName;
use Argtyper202511\Rector\TypeDeclarationDocblocks\NodeFinder\ReturnNodeFinder;
use Argtyper202511\Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForJsonArrayRector\AddReturnDocblockForJsonArrayRectorTest
 */
final class AddReturnDocblockForJsonArrayRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\TypeDeclarationDocblocks\NodeFinder\ReturnNodeFinder
     */
    private $returnNodeFinder;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer
     */
    private $usefulArrayTagNodeAnalyzer;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ReturnNodeFinder $returnNodeFinder, PhpDocTypeChanger $phpDocTypeChanger, ValueResolver $valueResolver, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->returnNodeFinder = $returnNodeFinder;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->valueResolver = $valueResolver;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @return docblock for array based on return of json_decode() return array', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function provide(string $contents): array
    {
        return json_decode($contents, true);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @return array<string, mixed>
     */
    public function provide(string $contents): array
    {
        return json_decode($contents, true);
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
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        // definitely not an array return
        if ($node->returnType instanceof Node && !$this->isName($node->returnType, 'array')) {
            return null;
        }
        $onlyReturnWithExpr = $this->returnNodeFinder->findOnlyReturnWithExpr($node);
        if (!$onlyReturnWithExpr instanceof Return_) {
            return null;
        }
        $returnedExpr = $onlyReturnWithExpr->expr;
        if (!$returnedExpr instanceof Expr) {
            return null;
        }
        if (!$this->isJsonDecodeToArray($returnedExpr)) {
            return null;
        }
        $classMethodDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($classMethodDocInfo->getReturnTagValue())) {
            return null;
        }
        $hasChanged = $this->phpDocTypeChanger->changeReturnType($node, $phpDocInfo, new ArrayType(new StringType(), new MixedType()));
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function isJsonDecodeToArray(Expr $expr): bool
    {
        if ($expr instanceof FuncCall) {
            if (!$this->isName($expr, 'json_decode')) {
                return \false;
            }
            if ($expr->isFirstClassCallable()) {
                return \false;
            }
            $arg = $expr->getArg('associative', 1);
            if (!$arg instanceof Arg) {
                return \false;
            }
            return $this->valueResolver->isTrue($arg->value);
        }
        if ($expr instanceof StaticCall) {
            if (!$this->isName($expr->class, NetteClassName::JSON)) {
                return \false;
            }
            if (!$this->isName($expr->name, 'decode')) {
                return \false;
            }
            if ($expr->isFirstClassCallable()) {
                return \false;
            }
            $arg = $expr->getArg('forceArrays', 1);
            if (!$arg instanceof Arg) {
                return \false;
            }
            return $this->valueResolver->isTrue($arg->value);
        }
        return \false;
    }
}
