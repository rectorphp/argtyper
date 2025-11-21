<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\VendorLocker\NodeVendorLocker;

use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Reflection\ExtendedFunctionVariant;
use Argtyper202511\PHPStan\Reflection\MethodReflection;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\Rector\FileSystem\FilePathHelper;
use Argtyper202511\Rector\NodeAnalyzer\MagicClassMethodAnalyzer;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
final class ClassMethodReturnTypeOverrideGuard
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\FileSystem\FilePathHelper
     */
    private $filePathHelper;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\MagicClassMethodAnalyzer
     */
    private $magicClassMethodAnalyzer;
    public function __construct(ReflectionResolver $reflectionResolver, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, FilePathHelper $filePathHelper, MagicClassMethodAnalyzer $magicClassMethodAnalyzer)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->filePathHelper = $filePathHelper;
        $this->magicClassMethodAnalyzer = $magicClassMethodAnalyzer;
    }
    public function shouldSkipClassMethod(ClassMethod $classMethod, Scope $scope): bool
    {
        if ($this->magicClassMethodAnalyzer->isUnsafeOverridden($classMethod)) {
            return \true;
        }
        // except magic check on above, early allow add return type on private method
        if ($classMethod->isPrivate()) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        if ($classReflection->isAbstract()) {
            return \true;
        }
        if ($classReflection->isInterface()) {
            return \true;
        }
        return !$this->isReturnTypeChangeAllowed($classMethod, $scope);
    }
    private function isReturnTypeChangeAllowed(ClassMethod $classMethod, Scope $scope): bool
    {
        // make sure return type is not protected by parent contract
        $parentClassMethodReflection = $this->parentClassMethodTypeOverrideGuard->getParentClassMethod($classMethod);
        // nothing to check
        if (!$parentClassMethodReflection instanceof MethodReflection) {
            return !$this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($classMethod);
        }
        $parametersAcceptor = ParametersAcceptorSelectorVariantsWrapper::select($parentClassMethodReflection, $classMethod, $scope);
        if ($parametersAcceptor instanceof ExtendedFunctionVariant && !$parametersAcceptor->getNativeReturnType() instanceof MixedType) {
            return \false;
        }
        $classReflection = $parentClassMethodReflection->getDeclaringClass();
        $fileName = $classReflection->getFileName();
        // probably internal
        if ($fileName === null) {
            return \false;
        }
        /*
         * Below verify that both current file name and parent file name is not in the /vendor/, if yes, then allowed.
         * This can happen when rector run into /vendor/ directory while child and parent both are there.
         *
         *  @see https://3v4l.org/Rc0RF#v8.0.13
         *
         *     - both in /vendor/ -> allowed
         *     - one of them in /vendor/ -> not allowed
         *     - both not in /vendor/ -> allowed
         */
        /** @var ClassReflection $currentClassReflection */
        $currentClassReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        /** @var string $currentFileName */
        $currentFileName = $currentClassReflection->getFileName();
        // child (current)
        $normalizedCurrentFileName = $this->filePathHelper->normalizePathAndSchema($currentFileName);
        $isCurrentInVendor = strpos($normalizedCurrentFileName, '/vendor/') !== \false;
        // parent
        $normalizedFileName = $this->filePathHelper->normalizePathAndSchema($fileName);
        $isParentInVendor = strpos($normalizedFileName, '/vendor/') !== \false;
        return $isCurrentInVendor && $isParentInVendor || !$isCurrentInVendor && !$isParentInVendor;
    }
}
