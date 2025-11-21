<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Attribute;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Argtyper202511\RectorPrefix202511\PHPUnit\Framework\Attributes\Depends;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Doctrine\NodeAnalyzer\AttrinationFinder;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\AddParamTypeFromDependsRector\AddParamTypeFromDependsRectorTest
 */
final class AddParamTypeFromDependsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttrinationFinder
     */
    private $attrinationFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, AttrinationFinder $attrinationFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->attrinationFinder = $attrinationFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add param type declaration based on @depends test method return type', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test(): \stdClass
    {
        return new \stdClass();
    }

    /**
     * @depends test
     */
    public function testAnother($someObject)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test(): \stdClass
    {
        return new \stdClass();
    }

    /**
     * @depends test
     */
    public function testAnother(\stdClass $someObject)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            if (\count($classMethod->params) !== 1) {
                continue;
            }
            $soleParam = $classMethod->getParams()[0];
            // already known type
            if ($soleParam->type instanceof Node) {
                continue;
            }
            $dependsReturnType = $this->resolveReturnTypeOfDependsMethod($classMethod, $node);
            if (!$dependsReturnType instanceof Node) {
                continue;
            }
            $soleParam->type = $dependsReturnType;
            $hasChanged = \true;
        }
        if ($hasChanged === \false) {
            return null;
        }
        return $node;
    }
    private function resolveReturnTypeOfDependsMethod(ClassMethod $classMethod, Class_ $class) : ?Node
    {
        $dependsMethodName = $this->resolveDependsAnnotationOrAttributeMethod($classMethod);
        if ($dependsMethodName === null || $dependsMethodName === '') {
            return null;
        }
        $dependsClassMethod = $class->getMethod($dependsMethodName);
        if (!$dependsClassMethod instanceof ClassMethod) {
            return null;
        }
        // resolve return type here
        return $dependsClassMethod->returnType;
    }
    private function resolveDependsAnnotationOrAttributeMethod(ClassMethod $classMethod) : ?string
    {
        $dependsAttribute = $this->attrinationFinder->getByOne($classMethod, Depends::class);
        if ($dependsAttribute instanceof Attribute) {
            $firstArg = $dependsAttribute->args[0];
            if ($firstArg->value instanceof String_) {
                $dependsMethodName = $firstArg->value->value;
                return \trim($dependsMethodName, '()');
            }
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $dependsTagValueNode = $phpDocInfo->getByName('depends');
        if (!$dependsTagValueNode instanceof PhpDocTagNode) {
            return null;
        }
        $dependsMethodName = (string) $dependsTagValueNode->value;
        return \trim($dependsMethodName, '()');
    }
}
