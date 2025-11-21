<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php84\Rector\Class_;

use Argtyper202511\PhpParser\Modifiers;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\PropertyHook;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\Rector\Configuration\Parameter\FeatureFlags;
use Argtyper202511\Rector\Php84\NodeFactory\PropertyHookFactory;
use Argtyper202511\Rector\Php84\NodeFinder\SetterAndGetterFinder;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php84\Rector\Class_\PropertyHookRector\PropertyHookRectorTest
 */
final class PropertyHookRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php84\NodeFinder\SetterAndGetterFinder
     */
    private $setterAndGetterFinder;
    /**
     * @readonly
     * @var \Rector\Php84\NodeFactory\PropertyHookFactory
     */
    private $propertyHookFactory;
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    public function __construct(SetterAndGetterFinder $setterAndGetterFinder, PropertyHookFactory $propertyHookFactory, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard)
    {
        $this->setterAndGetterFinder = $setterAndGetterFinder;
        $this->propertyHookFactory = $propertyHookFactory;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace getter/setter with property hook', [new CodeSample(<<<'CODE_SAMPLE'
final class Product
{
    private string $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = ucfirst($name);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class Product
{
    public string $name
    {
        get => $this->name;
        set($value) => $this->name = ucfirst($value);
    }
}

CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->isReadonly()) {
            return null;
        }
        // avoid breaking of child class getter/setter method use
        if (!$node->isFinal() && FeatureFlags::treatClassesAsFinal($node) === \false) {
            return null;
        }
        if ($this->hasMagicGetSetMethod($node)) {
            return null;
        }
        // nothing to hook to
        if ($node->getProperties() === []) {
            return null;
        }
        $classMethodsToRemove = [];
        foreach ($node->getProperties() as $property) {
            $propertyName = $this->getName($property);
            if ($property->isReadonly()) {
                continue;
            }
            $candidateClassMethods = $this->setterAndGetterFinder->findGetterAndSetterClassMethods($node, $propertyName);
            foreach ($candidateClassMethods as $candidateClassMethod) {
                if (\count((array) $candidateClassMethod->stmts) !== 1) {
                    continue;
                }
                // skip attributed methods
                if ($candidateClassMethod->attrGroups !== []) {
                    continue;
                }
                // avoid parent contract/method override
                if ($this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($candidateClassMethod)) {
                    continue;
                }
                $propertyHook = $this->propertyHookFactory->create($candidateClassMethod, $propertyName);
                if (!$propertyHook instanceof PropertyHook) {
                    continue;
                }
                if (!$property->isPublic()) {
                    $property->flags = Modifiers::PUBLIC;
                }
                $property->hooks[] = $propertyHook;
                $classMethodsToRemove[] = $candidateClassMethod;
            }
        }
        if ($classMethodsToRemove === []) {
            return null;
        }
        foreach ($node->stmts as $key => $classStmt) {
            if (!$classStmt instanceof ClassMethod) {
                continue;
            }
            if (!\in_array($classStmt, $classMethodsToRemove)) {
                continue;
            }
            unset($node->stmts[$key]);
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::PROPERTY_HOOKS;
    }
    private function hasMagicGetSetMethod(Class_ $class) : bool
    {
        $magicGetMethod = $class->getMethod(MethodName::__GET);
        if ($magicGetMethod instanceof ClassMethod) {
            return \true;
        }
        $magicSetMethod = $class->getMethod(MethodName::__SET);
        return $magicSetMethod instanceof ClassMethod;
    }
}
