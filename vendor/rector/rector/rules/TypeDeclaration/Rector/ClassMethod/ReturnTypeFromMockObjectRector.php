<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\IntersectionType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\Enum\ClassName;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromMockObjectRector\ReturnTypeFromMockObjectRectorTest
 */
final class ReturnTypeFromMockObjectRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
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
    public function __construct(BetterNodeFinder $betterNodeFinder, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ReturnAnalyzer $returnAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->returnAnalyzer = $returnAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add known property and return MockObject types', [new CodeSample(<<<'CODE_SAMPLE'
class SomeTest extends TestCase
{
    public function createSomeMock()
    {
        $someMock = $this->createMock(SomeClass::class);
        return $someMock;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeTest extends TestCase
{
    public function createSomeMock(): \PHPUnit\Framework\MockObject\MockObject
    {
        $someMock = $this->createMock(SomeClass::class);
        return $someMock;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        // type is already known
        if ($node->returnType instanceof Node) {
            return null;
        }
        if (!$this->isInsideTestCaseClass($scope)) {
            return null;
        }
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        // we need exactly 1 return
        $returns = $this->betterNodeFinder->findReturnsScoped($node);
        if (\count($returns) !== 1) {
            return null;
        }
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($node, $returns)) {
            return null;
        }
        /** @var Expr $expr */
        $expr = $returns[0]->expr;
        $returnType = $this->nodeTypeResolver->getNativeType($expr);
        if (!$this->isMockObjectType($returnType)) {
            return null;
        }
        $node->returnType = new FullyQualified(ClassName::MOCK_OBJECT);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    private function isIntersectionWithMockObjectType(Type $type) : bool
    {
        if (!$type instanceof IntersectionType) {
            return \false;
        }
        if (\count($type->getTypes()) !== 2) {
            return \false;
        }
        return \in_array(ClassName::MOCK_OBJECT, $type->getObjectClassNames());
    }
    private function isMockObjectType(Type $returnType) : bool
    {
        if ($returnType instanceof ObjectType && $returnType->isInstanceOf(ClassName::MOCK_OBJECT)->yes()) {
            return \true;
        }
        return $this->isIntersectionWithMockObjectType($returnType);
    }
    private function isInsideTestCaseClass(Scope $scope) : bool
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        // is phpunit test case?
        return $classReflection->is(ClassName::TEST_CASE_CLASS);
    }
}
