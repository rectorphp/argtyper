<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php83\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Attribute;
use Argtyper202511\PhpParser\Node\AttributeGroup;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Throw_;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassLike;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\NodeAnalyzer\ClassAnalyzer;
use Argtyper202511\Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Argtyper202511\Rector\PhpParser\AstResolver;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/marking_overriden_methods
 *
 * @see \Rector\Tests\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector\AddOverrideAttributeToOverriddenMethodsRectorTest
 */
final class AddOverrideAttributeToOverriddenMethodsRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @api
     * @var string
     */
    public const ALLOW_OVERRIDE_EMPTY_METHOD = 'allow_override_empty_method';
    /**
     * @var string
     */
    private const OVERRIDE_CLASS = 'Override';
    /**
     * @var bool
     */
    private $allowOverrideEmptyMethod = \false;
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function __construct(ReflectionProvider $reflectionProvider, ClassAnalyzer $classAnalyzer, PhpAttributeAnalyzer $phpAttributeAnalyzer, AstResolver $astResolver, ValueResolver $valueResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->classAnalyzer = $classAnalyzer;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->astResolver = $astResolver;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add override attribute to overridden methods', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class ParentClass
{
    public function foo()
    {
        echo 'default';
    }
}

final class ChildClass extends ParentClass
{
    public function foo()
    {
        echo 'override default';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class ParentClass
{
    public function foo()
    {
        echo 'default';
    }
}

final class ChildClass extends ParentClass
{
    #[\Override]
    public function foo()
    {
        echo 'override default';
    }
}
CODE_SAMPLE
, [self::ALLOW_OVERRIDE_EMPTY_METHOD => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration): void
    {
        $this->allowOverrideEmptyMethod = $configuration[self::ALLOW_OVERRIDE_EMPTY_METHOD] ?? \false;
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->hasChanged = \false;
        if ($this->classAnalyzer->isAnonymousClass($node)) {
            return null;
        }
        $className = (string) $this->getName($node);
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $parentClassReflections = $classReflection->getParents();
        if ($this->allowOverrideEmptyMethod) {
            $parentClassReflections = array_merge(
                $parentClassReflections,
                $classReflection->getInterfaces(),
                // place on last to ensure verify method exists on parent early
                // for non abstract method from trait
                $classReflection->getTraits()
            );
        }
        if ($parentClassReflections === []) {
            return null;
        }
        foreach ($node->getMethods() as $classMethod) {
            $this->processAddOverrideAttribute($classMethod, $parentClassReflections);
        }
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::OVERRIDE_ATTRIBUTE;
    }
    /**
     * @param ClassReflection[] $parentClassReflections
     */
    private function processAddOverrideAttribute(ClassMethod $classMethod, array $parentClassReflections): void
    {
        if ($this->shouldSkipClassMethod($classMethod)) {
            return;
        }
        /** @var string $classMethodName */
        $classMethodName = $this->getName($classMethod->name);
        // Private methods should be ignored
        $shouldAddOverride = \false;
        foreach ($parentClassReflections as $parentClassReflection) {
            if (!$parentClassReflection->hasNativeMethod($classMethod->name->toString())) {
                continue;
            }
            // ignore if it is a private method on the parent
            if (!$parentClassReflection->hasNativeMethod($classMethodName)) {
                continue;
            }
            $parentMethod = $parentClassReflection->getNativeMethod($classMethodName);
            if ($parentMethod->isPrivate()) {
                break;
            }
            if ($this->shouldSkipParentClassMethod($parentClassReflection, $classMethod)) {
                continue;
            }
            if ($parentClassReflection->isTrait() && !$parentMethod->isAbstract()) {
                break;
            }
            $shouldAddOverride = \true;
            break;
        }
        if ($shouldAddOverride) {
            $classMethod->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(self::OVERRIDE_CLASS))]);
            $this->hasChanged = \true;
        }
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if ($this->isName($classMethod->name, MethodName::CONSTRUCT)) {
            return \true;
        }
        if ($classMethod->isPrivate()) {
            return \true;
        }
        // ignore if it already uses the attribute
        return $this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, self::OVERRIDE_CLASS);
    }
    private function shouldSkipParentClassMethod(ClassReflection $parentClassReflection, ClassMethod $classMethod): bool
    {
        if ($this->allowOverrideEmptyMethod && $parentClassReflection->isBuiltIn()) {
            return \false;
        }
        // parse parent method, if it has some contents or not
        $parentClass = $this->astResolver->resolveClassFromClassReflection($parentClassReflection);
        if (!$parentClass instanceof ClassLike) {
            return \true;
        }
        $parentClassMethod = $parentClass->getMethod($classMethod->name->toString());
        if (!$parentClassMethod instanceof ClassMethod) {
            return \true;
        }
        if ($this->allowOverrideEmptyMethod) {
            return \false;
        }
        // just override abstract method also skipped on purpose
        // only grand child of abstract method that parent has content will have
        if ($parentClassMethod->isAbstract()) {
            return \true;
        }
        // has any stmts?
        if ($parentClassMethod->stmts === null || $parentClassMethod->stmts === []) {
            return \true;
        }
        if (count($parentClassMethod->stmts) === 1) {
            /** @var Stmt $soleStmt */
            $soleStmt = $parentClassMethod->stmts[0];
            // most likely, return null; is interface to be designed to override
            if ($soleStmt instanceof Return_ && $soleStmt->expr instanceof Expr && $this->valueResolver->isNull($soleStmt->expr)) {
                return \true;
            }
            if ($soleStmt instanceof Expression && $soleStmt->expr instanceof Throw_) {
                return \true;
            }
        }
        return \false;
    }
}
