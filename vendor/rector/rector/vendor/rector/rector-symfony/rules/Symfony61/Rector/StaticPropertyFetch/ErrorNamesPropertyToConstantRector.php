<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Symfony61\Rector\StaticPropertyFetch;

use Argtyper202511\PhpParser\Modifiers;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Const_;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticPropertyFetch;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassConst;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\Symfony\Enum\SymfonyClass;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Covers:
 * - https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.1.md#validator
 *
 * @see \Rector\Symfony\Tests\Symfony61\Rector\StaticPropertyFetch\ErrorNamesPropertyToConstantRector\ErrorNamesPropertyToConstantRectorTest
 */
final class ErrorNamesPropertyToConstantRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns old Constraint::$errorNames properties to use Constraint::ERROR_NAMES instead', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Validator\Constraints\NotBlank;

class SomeClass
{
    NotBlank::$errorNames

}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Validator\Constraints\NotBlank;

class SomeClass
{
    NotBlank::ERROR_NAMES

}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticPropertyFetch::class, Class_::class];
    }
    /**
     * @param StaticPropertyFetch|Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if (!$classReflection->is(SymfonyClass::VALIDATOR_CONSTRAINT)) {
            return null;
        }
        if ($node instanceof StaticPropertyFetch) {
            return $this->refactorStaticPropertyFetch($node, $classReflection);
        }
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Property) {
                continue;
            }
            if (!$stmt->isStatic()) {
                continue;
            }
            if (!$this->isName($stmt->props[0], 'errorNames')) {
                continue;
            }
            $node->stmts[$key] = $this->createClassConst($stmt, $stmt);
            return $node;
        }
        return null;
    }
    private function refactorStaticPropertyFetch(StaticPropertyFetch $node, ClassReflection $classReflection) : ?ClassConstFetch
    {
        if (!$this->isName($node->name, 'errorNames')) {
            return null;
        }
        $parentClass = $classReflection->getParentClass();
        if (!$parentClass instanceof ClassReflection) {
            return null;
        }
        return $this->nodeFactory->createClassConstFetch($parentClass->getName(), 'ERROR_NAMES');
    }
    private function createClassConst(Property $property, Property $stmt) : ClassConst
    {
        $propertyItem = $property->props[0];
        $const = new Const_('ERROR_NAMES', $propertyItem->default);
        $classConst = new ClassConst([$const], $stmt->flags & ~Modifiers::STATIC);
        $classConst->setDocComment($property->getDocComment());
        return $classConst;
    }
}
