<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\RemoveUnusedPrivateMethodRectorTest
 */
final class RemoveUnusedPrivateMethodRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer
     */
    private $isClassMethodUsedAnalyzer;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    public function __construct(IsClassMethodUsedAnalyzer $isClassMethodUsedAnalyzer, ReflectionResolver $reflectionResolver, BetterNodeFinder $betterNodeFinder, PhpDocInfoFactory $phpDocInfoFactory, PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->isClassMethodUsedAnalyzer = $isClassMethodUsedAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused private method', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
        return 5;
    }

    private function skip()
    {
        return 10;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
        return 5;
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
    public function refactor(Node $node): ?Node
    {
        $classMethods = $node->getMethods();
        if ($classMethods === []) {
            return null;
        }
        $filter = static function (ClassMethod $classMethod): bool {
            return $classMethod->isPrivate();
        };
        $privateMethods = array_filter($classMethods, $filter);
        if ($privateMethods === []) {
            return null;
        }
        if ($this->hasDynamicMethodCallOnFetchThis($classMethods)) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $collectionTestMethodsUsesPrivateProvider = $this->collectTestMethodsUsesPrivateDataProvider($classReflection, $node, $classMethods);
        $hasChanged = \false;
        $scope = ScopeFetcher::fetch($node);
        foreach ($privateMethods as $privateMethod) {
            if ($this->shouldSkip($privateMethod, $classReflection)) {
                continue;
            }
            if ($this->isClassMethodUsedAnalyzer->isClassMethodUsed($node, $privateMethod, $scope)) {
                continue;
            }
            if (in_array($this->getName($privateMethod), $collectionTestMethodsUsesPrivateProvider, \true)) {
                continue;
            }
            unset($node->stmts[$privateMethod->getAttribute(AttributeKey::STMT_KEY)]);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param ClassMethod[] $classMethods
     * @return string[]
     */
    private function collectTestMethodsUsesPrivateDataProvider(ClassReflection $classReflection, Class_ $class, array $classMethods): array
    {
        if (!$classReflection->is('Argtyper202511\PHPUnit\Framework\TestCase')) {
            return [];
        }
        $privateMethods = [];
        foreach ($classMethods as $classMethod) {
            // test method only public, but may use private data provider
            // so verify @dataProvider and #[\PHPUnit\Framework\Attributes\DataProvider] only on public methods
            if (!$classMethod->isPublic()) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
            if ($phpDocInfo instanceof PhpDocInfo && $phpDocInfo->hasByName('dataProvider')) {
                $dataProvider = $phpDocInfo->getByName('dataProvider');
                if ($dataProvider instanceof PhpDocTagNode && $dataProvider->value instanceof GenericTagValueNode) {
                    $dataProviderMethod = $class->getMethod($dataProvider->value->value);
                    if ($dataProviderMethod instanceof ClassMethod && $dataProviderMethod->isPrivate()) {
                        $privateMethods[] = $dataProvider->value->value;
                    }
                }
            }
            if ($this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, 'Argtyper202511\PHPUnit\Framework\Attributes\DataProvider')) {
                foreach ($classMethod->attrGroups as $attrGroup) {
                    foreach ($attrGroup->attrs as $attr) {
                        if ($attr->name->toString() === 'PHPUnit\Framework\Attributes\DataProvider') {
                            $argValue = $attr->args[0]->value->value ?? '';
                            if (is_string($argValue)) {
                                $dataProviderMethod = $class->getMethod($argValue);
                                if ($dataProviderMethod instanceof ClassMethod && $dataProviderMethod->isPrivate()) {
                                    $privateMethods[] = $argValue;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $privateMethods;
    }
    private function shouldSkip(ClassMethod $classMethod, ?ClassReflection $classReflection): bool
    {
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        // unreliable to detect trait, interface, anonymous class: doesn't make sense
        if ($classReflection->isTrait()) {
            return \true;
        }
        if ($classReflection->isInterface()) {
            return \true;
        }
        if ($classReflection->isAnonymous()) {
            return \true;
        }
        // skip magic methods - @see https://www.php.net/manual/en/language.oop5.magic.php
        if ($classMethod->isMagic()) {
            return \true;
        }
        return $classReflection->hasMethod(MethodName::CALL);
    }
    /**
     * @param ClassMethod[] $classMethods
     */
    private function hasDynamicMethodCallOnFetchThis(array $classMethods): bool
    {
        foreach ($classMethods as $classMethod) {
            $isFound = (bool) $this->betterNodeFinder->findFirst((array) $classMethod->getStmts(), function (Node $subNode): bool {
                if (!$subNode instanceof MethodCall) {
                    return \false;
                }
                if (!$subNode->var instanceof Variable) {
                    return \false;
                }
                if (!$this->isName($subNode->var, 'this')) {
                    return \false;
                }
                return $subNode->name instanceof Variable;
            });
            if ($isFound) {
                return \true;
            }
        }
        return \false;
    }
}
