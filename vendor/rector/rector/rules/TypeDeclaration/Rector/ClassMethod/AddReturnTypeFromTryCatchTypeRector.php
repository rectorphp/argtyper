<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Stmt\Catch_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Finally_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PhpParser\Node\Stmt\TryCatch;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Argtyper202511\Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeFromTryCatchTypeRector\AddReturnTypeFromTryCatchTypeRectorTest
 */
final class AddReturnTypeFromTryCatchTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer
     */
    private $returnAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(TypeComparator $typeComparator, StaticTypeMapper $staticTypeMapper, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ReturnAnalyzer $returnAnalyzer, BetterNodeFinder $betterNodeFinder)
    {
        $this->typeComparator = $typeComparator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->returnAnalyzer = $returnAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add known type declarations based on first-level try/catch return values', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        try {
            return 1;
        } catch (\Exception $e) {
            return 2;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): int
    {
        try {
            return 1;
        } catch (\Exception $e) {
            return 2;
        }
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
        $scope = ScopeFetcher::fetch($node);
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        // already known type
        if ($node->returnType instanceof Node) {
            return null;
        }
        $tryReturnType = null;
        $catchReturnTypes = [];
        $returns = $this->betterNodeFinder->findReturnsScoped($node);
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($node, $returns)) {
            return null;
        }
        foreach ((array) $node->stmts as $classMethodStmt) {
            if (!$classMethodStmt instanceof TryCatch) {
                continue;
            }
            // skip if there is no catch
            if ($classMethodStmt->catches === []) {
                continue;
            }
            $tryCatch = $classMethodStmt;
            $tryReturnType = $this->matchReturnType($tryCatch);
            foreach ($tryCatch->catches as $catch) {
                $currentCatchType = $this->matchReturnType($catch);
                // each catch must have type
                if (!$currentCatchType instanceof Type) {
                    return null;
                }
                $catchReturnTypes[] = $currentCatchType;
            }
            if ($tryCatch->finally instanceof Finally_) {
                $finallyReturnType = $this->matchReturnType($tryCatch->finally);
                if ($finallyReturnType instanceof Type) {
                    $catchReturnTypes[] = $finallyReturnType;
                }
            }
        }
        if (!$tryReturnType instanceof Type) {
            return null;
        }
        foreach ($catchReturnTypes as $catchReturnType) {
            if (!$this->typeComparator->areTypesEqual($catchReturnType, $tryReturnType)) {
                return null;
            }
        }
        $returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($tryReturnType, TypeKind::RETURN);
        if (!$returnType instanceof Node) {
            return null;
        }
        $node->returnType = $returnType;
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt\TryCatch|\PhpParser\Node\Stmt\Catch_|\PhpParser\Node\Stmt\Finally_ $tryOrCatchOrFinally
     */
    private function matchReturnType($tryOrCatchOrFinally): ?Type
    {
        foreach ($tryOrCatchOrFinally->stmts as $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            if (!$stmt->expr instanceof Expr) {
                continue;
            }
            return $this->nodeTypeResolver->getNativeType($stmt->expr);
        }
        return null;
    }
}
