<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\StaticType;
use Argtyper202511\PHPStan\Type\ThisType;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictFluentReturnRector\ReturnTypeFromStrictFluentReturnRectorTest
 */
final class ReturnTypeFromStrictFluentReturnRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ReflectionResolver $reflectionResolver, ReturnTypeInferer $returnTypeInferer, PhpVersionProvider $phpVersionProvider)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->reflectionResolver = $reflectionResolver;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add return type from strict return $this', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return $this;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): self
    {
        return $this;
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
        return [ClassMethod::class];
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::HAS_RETURN_TYPE;
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        // already typed → skip
        if ($node->returnType instanceof Node) {
            return null;
        }
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $returnType = $this->returnTypeInferer->inferFunctionLike($node);
        if ($returnType instanceof StaticType && $returnType->getStaticObjectType()->getClassName() === $classReflection->getName()) {
            return $this->processAddReturnSelfOrStatic($node, $classReflection);
        }
        if ($returnType instanceof ObjectType && $returnType->getClassName() === $classReflection->getName()) {
            $node->returnType = new Name('self');
            return $node;
        }
        if (!$returnType instanceof ThisType) {
            return null;
        }
        return $this->processAddReturnSelfOrStatic($node, $classReflection);
    }
    private function processAddReturnSelfOrStatic(ClassMethod $classMethod, ClassReflection $classReflection) : ClassMethod
    {
        $classMethod->returnType = $this->shouldSelf($classReflection) ? new Name('self') : new Name('static');
        return $classMethod;
    }
    private function shouldSelf(ClassReflection $classReflection) : bool
    {
        if ($classReflection->isAnonymous()) {
            return \true;
        }
        if ($classReflection->isFinalByKeyword()) {
            return \true;
        }
        return !$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::STATIC_RETURN_TYPE);
    }
}
