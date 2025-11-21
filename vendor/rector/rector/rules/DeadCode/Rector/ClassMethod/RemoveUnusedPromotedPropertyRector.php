<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\TraitUse;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\DeadCode\NodeAnalyzer\PropertyWriteonlyAnalyzer;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\PhpParser\NodeFinder\PropertyFetchFinder;
use Argtyper202511\Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\ValueObject\Visibility;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector\RemoveUnusedPromotedPropertyRectorTest
 */
final class RemoveUnusedPromotedPropertyRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\NodeFinder\PropertyFetchFinder
     */
    private $propertyFetchFinder;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\PropertyWriteonlyAnalyzer
     */
    private $propertyWriteonlyAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    public function __construct(PropertyFetchFinder $propertyFetchFinder, VisibilityManipulator $visibilityManipulator, PropertyWriteonlyAnalyzer $propertyWriteonlyAnalyzer, BetterNodeFinder $betterNodeFinder, ReflectionResolver $reflectionResolver, PhpDocInfoFactory $phpDocInfoFactory, PhpDocTagRemover $phpDocTagRemover, DocBlockUpdater $docBlockUpdater)
    {
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->propertyWriteonlyAnalyzer = $propertyWriteonlyAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->reflectionResolver = $reflectionResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused promoted property', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private $someUnusedDependency,
        private $usedDependency
    ) {
    }

    public function getUsedDependency()
    {
        return $this->usedDependency;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private $usedDependency
    ) {
    }

    public function getUsedDependency()
    {
        return $this->usedDependency;
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
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        if ($constructClassMethod->params === []) {
            return null;
        }
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $hasChanged = \false;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($constructClassMethod);
        foreach ($constructClassMethod->params as $key => $param) {
            // only private local scope; removing public property might be dangerous
            if (!$this->visibilityManipulator->hasVisibility($param, Visibility::PRIVATE)) {
                continue;
            }
            $paramName = $this->getName($param);
            $propertyFetches = $this->propertyFetchFinder->findLocalPropertyFetchesByName($node, $paramName);
            if ($propertyFetches !== []) {
                continue;
            }
            if (!$this->propertyWriteonlyAnalyzer->arePropertyFetchesExclusivelyBeingAssignedTo($propertyFetches)) {
                continue;
            }
            // always changed on below code
            $hasChanged = \true;
            // is variable used? only remove property, keep param
            $variable = $this->betterNodeFinder->findVariableOfName((array) $constructClassMethod->stmts, $paramName);
            if ($variable instanceof Variable) {
                $param->flags = 0;
                continue;
            }
            if ($phpDocInfo instanceof PhpDocInfo) {
                $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);
                if ($paramTagValueNode instanceof ParamTagValueNode) {
                    $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $paramTagValueNode);
                    $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($constructClassMethod);
                }
            }
            // remove param
            unset($constructClassMethod->params[$key]);
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::PROPERTY_PROMOTION;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        if ($class->attrGroups !== []) {
            return \true;
        }
        $magicGetMethod = $class->getMethod(MethodName::__GET);
        if ($magicGetMethod instanceof ClassMethod) {
            return \true;
        }
        foreach ($class->stmts as $stmt) {
            if ($stmt instanceof TraitUse) {
                return \true;
            }
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($class);
        if ($classReflection instanceof ClassReflection) {
            $interfaces = $classReflection->getInterfaces();
            foreach ($interfaces as $interface) {
                if ($interface->hasNativeMethod(MethodName::CONSTRUCT)) {
                    return \true;
                }
            }
        }
        return $this->propertyWriteonlyAnalyzer->hasClassDynamicPropertyNames($class);
    }
}
