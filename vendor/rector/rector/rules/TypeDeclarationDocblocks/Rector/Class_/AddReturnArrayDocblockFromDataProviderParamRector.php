<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\TypeDeclarationDocblocks\NodeFinder\DataProviderMethodsFinder;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\Class_\AddReturnArrayDocblockFromDataProviderParamRector\AddReturnArrayDocblockFromDataProviderParamRectorTest
 */
final class AddReturnArrayDocblockFromDataProviderParamRector extends AbstractRector
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
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclarationDocblocks\NodeFinder\DataProviderMethodsFinder
     */
    private $dataProviderMethodsFinder;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper, TestsNodeAnalyzer $testsNodeAnalyzer, DataProviderMethodsFinder $dataProviderMethodsFinder, DocBlockUpdater $docBlockUpdater)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->dataProviderMethodsFinder = $dataProviderMethodsFinder;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add @return array return from data provider param type', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    /**
     * @dataProvider provideNames()
     */
    public function test(string $name)
    {
    }

    public function provideNames(): array
    {
        return ['John', 'Jane'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    /**
     * @dataProvider provideNames()
     */
    public function test(string $name)
    {
    }

    /**
     * @return string[]
     */
    public function provideNames(): array
    {
        return ['John', 'Jane'];
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
            if (!$this->testsNodeAnalyzer->isTestClassMethod($classMethod)) {
                continue;
            }
            // sole param required
            if (\count($classMethod->getParams()) !== 1) {
                continue;
            }
            $dataProviderNodes = $this->dataProviderMethodsFinder->findDataProviderNodes($node, $classMethod);
            $paramTypesByPosition = [];
            foreach ($classMethod->getParams() as $position => $param) {
                if (!$param->type instanceof Node) {
                    continue;
                }
                $paramTypesByPosition[$position] = $param->type;
            }
            if ($paramTypesByPosition === []) {
                continue;
            }
            foreach ($dataProviderNodes->getClassMethods() as $dataProviderClassMethod) {
                if (!$dataProviderClassMethod->returnType instanceof Identifier) {
                    continue;
                }
                if (!$this->isName($dataProviderClassMethod->returnType, 'array')) {
                    continue;
                }
                // already set return tag
                $dataProviderPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($dataProviderClassMethod);
                if ($dataProviderPhpDocInfo->getReturnTagValue() instanceof ReturnTagValueNode) {
                    continue;
                }
                $paramTypeNode = $paramTypesByPosition[0];
                $returnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($paramTypeNode);
                $arrayReturnType = new ArrayType(new MixedType(), $returnType);
                $arrayReturnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($arrayReturnType);
                $returnTagValueNode = new ReturnTagValueNode($arrayReturnTypeNode, '');
                $dataProviderPhpDocInfo->addTagValueNode($returnTagValueNode);
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($dataProviderClassMethod);
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
