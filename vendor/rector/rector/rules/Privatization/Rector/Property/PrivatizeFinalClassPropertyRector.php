<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Privatization\Rector\Property;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\Privatization\Guard\ParentPropertyLookupGuard;
use Argtyper202511\Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Rector\ValueObject\Visibility;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector\PrivatizeFinalClassPropertyRectorTest
 */
final class PrivatizeFinalClassPropertyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\Privatization\Guard\ParentPropertyLookupGuard
     */
    private $parentPropertyLookupGuard;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(VisibilityManipulator $visibilityManipulator, ParentPropertyLookupGuard $parentPropertyLookupGuard, ReflectionResolver $reflectionResolver)
    {
        $this->visibilityManipulator = $visibilityManipulator;
        $this->parentPropertyLookupGuard = $parentPropertyLookupGuard;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change property to private if possible', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    protected $value;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private $value;
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
        if (!$node->isFinal()) {
            return null;
        }
        $hasChanged = \false;
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        foreach ($node->getProperties() as $property) {
            if ($this->shouldSkipProperty($property)) {
                continue;
            }
            if (!$this->parentPropertyLookupGuard->isLegal($property, $classReflection)) {
                continue;
            }
            $this->visibilityManipulator->makePrivate($property);
            $hasChanged = \true;
        }
        $construct = $node->getMethod(MethodName::CONSTRUCT);
        if ($construct instanceof ClassMethod) {
            foreach ($construct->params as $param) {
                if (!$param->isPromoted()) {
                    continue;
                }
                if (!$this->visibilityManipulator->hasVisibility($param, Visibility::PROTECTED)) {
                    continue;
                }
                if (!$this->parentPropertyLookupGuard->isLegal((string) $this->getName($param), $classReflection)) {
                    continue;
                }
                $this->visibilityManipulator->makePrivate($param);
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkipProperty(Property $property) : bool
    {
        if (\count($property->props) !== 1) {
            return \true;
        }
        return !$property->isProtected();
    }
}
