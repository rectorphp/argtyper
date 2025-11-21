<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\PhpParser\NodeFinder\LocalMethodCallFinder;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer\CallTypesResolver;
use Argtyper202511\Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
use Argtyper202511\Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\Class_\ClassMethodArrayDocblockParamFromLocalCallsRector\ClassMethodArrayDocblockParamFromLocalCallsRectorTest
 */
final class ClassMethodArrayDocblockParamFromLocalCallsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\CallTypesResolver
     */
    private $callTypesResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\NodeFinder\LocalMethodCallFinder
     */
    private $localMethodCallFinder;
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
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, CallTypesResolver $callTypesResolver, LocalMethodCallFinder $localMethodCallFinder, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->callTypesResolver = $callTypesResolver;
        $this->localMethodCallFinder = $localMethodCallFinder;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
        $this->nodeDocblockTypeDecorator = $nodeDocblockTypeDecorator;
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @param array docblock to a class method based on local call types', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function go()
    {
        $this->run(['item1', 'item2']);
    }

    private function run(array $items)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function go()
    {
        $this->run(['item1', 'item2']);
    }

    /**
     * @param string[] $items
     */
    private function run(array $items)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->getParams() === []) {
                continue;
            }
            $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $methodCalls = $this->localMethodCallFinder->match($node, $classMethod);
            $classMethodParameterTypes = $this->callTypesResolver->resolveTypesFromCalls($methodCalls);
            foreach ($classMethod->getParams() as $parameterPosition => $param) {
                if (!$this->hasParamArrayType($param)) {
                    continue;
                }
                $parameterName = $this->getName($param);
                $parameterTagValueNode = $classMethodPhpDocInfo->getParamTagValueByName($parameterName);
                // already known, skip
                if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($parameterTagValueNode)) {
                    continue;
                }
                if ($parameterTagValueNode instanceof ParamTagValueNode && $classMethod->isPublic() && $this->usefulArrayTagNodeAnalyzer->isMixedArray($parameterTagValueNode->type)) {
                    // on public method, skip if there is mixed[], as caller can be anything
                    continue;
                }
                $resolvedParameterType = $classMethodParameterTypes[$parameterPosition] ?? $classMethodParameterTypes[$parameterName] ?? null;
                if (!$resolvedParameterType instanceof Type) {
                    continue;
                }
                // in case of array type declaration, null cannot be passed or is already casted
                $resolvedParameterType = TypeCombinator::removeNull($resolvedParameterType);
                $hasClassMethodChanged = $this->nodeDocblockTypeDecorator->decorateGenericIterableParamType($resolvedParameterType, $classMethodPhpDocInfo, $classMethod, $param, $parameterName);
                if ($hasClassMethodChanged) {
                    $hasChanged = \true;
                }
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function hasParamArrayType(Param $param): bool
    {
        if (!$param->type instanceof Node) {
            return \false;
        }
        return $this->isName($param->type, 'array');
    }
}
