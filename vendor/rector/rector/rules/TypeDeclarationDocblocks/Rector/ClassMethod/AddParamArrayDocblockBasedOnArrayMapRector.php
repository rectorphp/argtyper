<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
use Rector\TypeDeclarationDocblocks\NodeFinder\ArrayMapClosureExprFinder;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockBasedOnArrayMapRector\AddParamArrayDocblockBasedOnArrayMapRectorTest
 */
final class AddParamArrayDocblockBasedOnArrayMapRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\TypeDeclarationDocblocks\NodeFinder\ArrayMapClosureExprFinder
     */
    private $arrayMapClosureExprFinder;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
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
    public function __construct(ArrayMapClosureExprFinder $arrayMapClosureExprFinder, StaticTypeMapper $staticTypeMapper, PhpDocInfoFactory $phpDocInfoFactory, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator)
    {
        $this->arrayMapClosureExprFinder = $arrayMapClosureExprFinder;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
        $this->nodeDocblockTypeDecorator = $nodeDocblockTypeDecorator;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @param array docblock if array_map is used on the parameter', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(array $names): void
    {
        $names = array_map(fn(string $name) => trim($name), $names);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @param string[] $names
     */
    public function run(array $names): void
    {
        $names = array_map(fn(string $name) => trim($name), $names);
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
        if ($node->getParams() === []) {
            return null;
        }
        $hasChanged = \false;
        $functionPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        foreach ($node->params as $param) {
            // handle only arrays
            if (!$this->isArrayParam($param)) {
                continue;
            }
            $paramName = $this->getName($param);
            $arrayMapClosures = $this->arrayMapClosureExprFinder->findByVariableName($node, $paramName);
            if ($arrayMapClosures === []) {
                continue;
            }
            foreach ($arrayMapClosures as $arrayMapClosure) {
                $params = $arrayMapClosure->getParams();
                if ($params === []) {
                    continue;
                }
                $firstParam = $params[0];
                $paramTypeNode = $firstParam->type;
                if ($paramTypeNode === null) {
                    continue;
                }
                if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($functionPhpDocInfo->getParamTagValueByName($paramName))) {
                    continue;
                }
                $paramType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($paramTypeNode);
                $arrayParamType = new ArrayType(new MixedType(), $paramType);
                if ($this->nodeDocblockTypeDecorator->decorateGenericIterableParamType($arrayParamType, $functionPhpDocInfo, $node, $param, $paramName)) {
                    $hasChanged = \true;
                }
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function isArrayParam(Param $param): bool
    {
        if (!$param->type instanceof Identifier) {
            return \false;
        }
        return $this->isName($param->type, 'array');
    }
}
