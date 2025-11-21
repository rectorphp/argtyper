<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\PHPUnit\Enum\PHPUnitClassName;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
final class TestsNodeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @var string[]
     */
    private const TEST_CASE_OBJECT_CLASSES = [PHPUnitClassName::TEST_CASE, PHPUnitClassName::TEST_CASE_LEGACY];
    public function __construct(NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver, PhpDocInfoFactory $phpDocInfoFactory, ReflectionResolver $reflectionResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function isInTestClass(Node $node): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        foreach (self::TEST_CASE_OBJECT_CLASSES as $testCaseObjectClass) {
            if ($classReflection->is($testCaseObjectClass)) {
                return \true;
            }
        }
        return \false;
    }
    public function isTestClassMethod(ClassMethod $classMethod): bool
    {
        if (!$classMethod->isPublic()) {
            return \false;
        }
        if (strncmp($classMethod->name->toString(), 'test', strlen('test')) === 0) {
            return \true;
        }
        foreach ($classMethod->getAttrGroups() as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if ($attribute->name->toString() === 'PHPUnit\Framework\Attributes\Test') {
                    return \true;
                }
            }
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        return $phpDocInfo->hasByName('test');
    }
    public function isAssertMethodCallName(Node $node, string $name): bool
    {
        if ($node instanceof StaticCall) {
            $callerType = $this->nodeTypeResolver->getType($node->class);
        } elseif ($node instanceof MethodCall) {
            $callerType = $this->nodeTypeResolver->getType($node->var);
        } else {
            return \false;
        }
        $assertObjectType = new ObjectType('Argtyper202511\PHPUnit\Framework\Assert');
        if (!$assertObjectType->isSuperTypeOf($callerType)->yes()) {
            return \false;
        }
        /** @var StaticCall|MethodCall $node */
        return $this->nodeNameResolver->isName($node->name, $name);
    }
    /**
     * @param string[] $names
     */
    public function isPHPUnitMethodCallNames(Node $node, array $names): bool
    {
        if (!$this->isPHPUnitTestCaseCall($node)) {
            return \false;
        }
        /** @var MethodCall|StaticCall $node */
        return $this->nodeNameResolver->isNames($node->name, $names);
    }
    public function isPHPUnitTestCaseCall(Node $node): bool
    {
        if ($node instanceof MethodCall) {
            return $this->isInTestClass($node);
        }
        if ($node instanceof StaticCall) {
            $classType = $this->nodeTypeResolver->getType($node->class);
            if ($classType instanceof ObjectType) {
                if ($classType->isInstanceOf(PHPUnitClassName::TEST_CASE)->yes()) {
                    return \true;
                }
                if ($classType->isInstanceOf(PHPUnitClassName::ASSERT)->yes()) {
                    return \true;
                }
            }
        }
        return \false;
    }
}
