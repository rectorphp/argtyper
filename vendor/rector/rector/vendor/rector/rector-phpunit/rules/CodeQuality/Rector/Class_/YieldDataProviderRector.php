<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\AssignOp;
use Argtyper202511\PhpParser\Node\Expr\AssignRef;
use Argtyper202511\PhpParser\Node\Expr\YieldFrom;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Argtyper202511\PHPStan\Type\Generic\GenericObjectType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\PhpParser\NodeTransformer;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\PHPUnit\NodeFinder\DataProviderClassMethodFinder;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://medium.com/tech-tajawal/use-memory-gently-with-yield-in-php-7e62e2480b8d
 * @changelog https://3v4l.org/5PJid
 *
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\YieldDataProviderRector\YieldDataProviderRectorTest
 */
final class YieldDataProviderRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\NodeTransformer
     */
    private $nodeTransformer;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeFinder\DataProviderClassMethodFinder
     */
    private $dataProviderClassMethodFinder;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer
     */
    private $isClassMethodUsedAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    public function __construct(NodeTransformer $nodeTransformer, TestsNodeAnalyzer $testsNodeAnalyzer, DataProviderClassMethodFinder $dataProviderClassMethodFinder, PhpDocInfoFactory $phpDocInfoFactory, IsClassMethodUsedAnalyzer $isClassMethodUsedAnalyzer, PhpDocTypeChanger $phpDocTypeChanger, DocBlockUpdater $docBlockUpdater)
    {
        $this->nodeTransformer = $nodeTransformer;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->dataProviderClassMethodFinder = $dataProviderClassMethodFinder;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->isClassMethodUsedAnalyzer = $isClassMethodUsedAnalyzer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns array return to yield in data providers', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest implements TestCase
{
    public static function provideData()
    {
        return [
            ['some text']
        ];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest implements TestCase
{
    public static function provideData()
    {
        yield ['some text'];
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Class_
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        $dataProviderClassMethods = $this->dataProviderClassMethodFinder->find($node);
        foreach ($dataProviderClassMethods as $dataProviderClassMethod) {
            $array = $this->collectReturnArrayNodesFromClassMethod($dataProviderClassMethod);
            if (!$array instanceof Array_) {
                continue;
            }
            $scope = ScopeFetcher::fetch($node);
            if ($this->isClassMethodUsedAnalyzer->isClassMethodUsed($node, $dataProviderClassMethod, $scope)) {
                continue;
            }
            $this->transformArrayToYieldsOnMethodNode($dataProviderClassMethod, $array);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function collectReturnArrayNodesFromClassMethod(ClassMethod $classMethod): ?Array_
    {
        if ($classMethod->stmts === null) {
            return null;
        }
        $yieldedFromExpr = null;
        foreach ($classMethod->stmts as $statement) {
            if ($statement instanceof Expression) {
                $statement = $statement->expr;
            }
            if ($statement instanceof Return_) {
                $returnedExpr = $statement->expr;
                if (!$returnedExpr instanceof Array_) {
                    return null;
                }
                return $returnedExpr;
            }
            if ($statement instanceof YieldFrom) {
                if (!$statement->expr instanceof Array_) {
                    return null;
                }
                if ($yieldedFromExpr instanceof Array_) {
                    return null;
                }
                $yieldedFromExpr = $statement->expr;
            } elseif (!$statement instanceof Assign && !$statement instanceof AssignRef && !$statement instanceof AssignOp) {
                return null;
            }
        }
        return $yieldedFromExpr;
    }
    private function transformArrayToYieldsOnMethodNode(ClassMethod $classMethod, Array_ $array): void
    {
        $yields = $this->nodeTransformer->transformArrayToYields($array);
        $this->removeReturnTag($classMethod);
        // change return typehint
        $classMethod->returnType = new FullyQualified('Iterator');
        $commentReturn = [];
        foreach ((array) $classMethod->stmts as $key => $classMethodStmt) {
            if ($classMethodStmt instanceof Expression) {
                $classMethodStmt = $classMethodStmt->expr;
            }
            if (!$classMethodStmt instanceof Return_ && !$classMethodStmt instanceof YieldFrom) {
                continue;
            }
            $commentReturn = $classMethodStmt->getAttribute(AttributeKey::COMMENTS) ?? [];
            unset($classMethod->stmts[$key]);
        }
        if (isset($yields[0])) {
            $yields[0]->setAttribute(AttributeKey::COMMENTS, array_merge($commentReturn, $yields[0]->getAttribute(AttributeKey::COMMENTS) ?? []));
        }
        $classMethod->stmts = array_merge((array) $classMethod->stmts, $yields);
    }
    private function removeReturnTag(ClassMethod $classMethod): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        if (!$phpDocInfo->getReturnTagValue() instanceof ReturnTagValueNode) {
            return;
        }
        if ($phpDocInfo->getReturnType()->isArray()->yes()) {
            $keyType = $phpDocInfo->getReturnType()->getIterableKeyType();
            $itemType = $phpDocInfo->getReturnType()->getIterableValueType();
            $this->phpDocTypeChanger->changeReturnType($classMethod, $phpDocInfo, new GenericObjectType('Iterator', [$keyType, $itemType]));
        } else {
            $phpDocInfo->removeByType(ReturnTagValueNode::class);
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
        }
    }
}
