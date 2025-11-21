<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\Isset_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanOr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\Isset_;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Reflection\Php\PhpPropertyReflection;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\IssetOnPropertyObjectToPropertyExistsRectorTest
 */
final class IssetOnPropertyObjectToPropertyExistsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ReflectionProvider $reflectionProvider, ReflectionResolver $reflectionResolver, ValueResolver $valueResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change isset on property object to `property_exists()` and not null check', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private $x;

    public function run(): void
    {
        isset($this->x);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    private $x;

    public function run(): void
    {
        property_exists($this, 'x') && $this->x !== null;
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
        return [Isset_::class, BooleanNot::class];
    }
    /**
     * @param Isset_|BooleanNot $node
     */
    public function refactor(Node $node) : ?Node
    {
        $isNegated = \false;
        if ($node instanceof BooleanNot) {
            if ($node->expr instanceof Isset_) {
                $isNegated = \true;
                $isset = $node->expr;
            } else {
                return null;
            }
        } else {
            $isset = $node;
        }
        $newNodes = [];
        foreach ($isset->vars as $issetExpr) {
            if (!$issetExpr instanceof PropertyFetch) {
                continue;
            }
            // has property PHP 7.4 type?
            if ($this->shouldSkipForPropertyTypeDeclaration($issetExpr)) {
                continue;
            }
            // Ignore dynamically accessed properties ($o->$p)
            $propertyFetchName = $this->getName($issetExpr->name);
            if (!\is_string($propertyFetchName)) {
                continue;
            }
            $classReflection = $this->matchPropertyTypeClassReflection($issetExpr);
            if (!$classReflection instanceof ClassReflection) {
                continue;
            }
            if ($classReflection->hasNativeMethod(MethodName::ISSET)) {
                continue;
            }
            // possibly by docblock
            if ($issetExpr->var instanceof ArrayDimFetch) {
                continue;
            }
            if (!$classReflection->hasInstanceProperty($propertyFetchName) || $classReflection->isBuiltin()) {
                $newNodes[] = $this->replaceToPropertyExistsWithNullCheck($issetExpr->var, $propertyFetchName, $issetExpr, $isNegated);
            } elseif ($isNegated) {
                $newNodes[] = $this->createIdenticalToNull($issetExpr);
            } else {
                $newNodes[] = $this->createNotIdenticalToNull($issetExpr);
            }
        }
        return $this->nodeFactory->createReturnBooleanAnd($newNodes);
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BinaryOp\BooleanOr
     */
    private function replaceToPropertyExistsWithNullCheck(Expr $expr, string $property, PropertyFetch $propertyFetch, bool $isNegated)
    {
        $args = [new Arg($expr), new Arg(new String_($property))];
        $propertyExistsFuncCall = $this->nodeFactory->createFuncCall('property_exists', $args);
        if ($isNegated) {
            $booleanNot = new BooleanNot($propertyExistsFuncCall);
            return new BooleanOr($booleanNot, $this->createIdenticalToNull($propertyFetch));
        }
        return new BooleanAnd($propertyExistsFuncCall, $this->createNotIdenticalToNull($propertyFetch));
    }
    private function createNotIdenticalToNull(PropertyFetch $propertyFetch) : NotIdentical
    {
        return new NotIdentical($propertyFetch, $this->nodeFactory->createNull());
    }
    private function shouldSkipForPropertyTypeDeclaration(PropertyFetch $propertyFetch) : bool
    {
        if (!$propertyFetch->name instanceof Identifier) {
            return \true;
        }
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($propertyFetch);
        if (!$phpPropertyReflection instanceof PhpPropertyReflection) {
            return \false;
        }
        $propertyType = $phpPropertyReflection->getNativeType();
        if ($propertyType instanceof MixedType) {
            return \false;
        }
        if (!TypeCombinator::containsNull($propertyType)) {
            return \true;
        }
        $nativeReflectionProperty = $phpPropertyReflection->getNativeReflection();
        if (!$nativeReflectionProperty->hasDefaultValue()) {
            return \true;
        }
        $defaultValueExpr = $nativeReflectionProperty->getDefaultValueExpression();
        return !$this->valueResolver->isNull($defaultValueExpr);
    }
    private function createIdenticalToNull(PropertyFetch $propertyFetch) : Identical
    {
        return new Identical($propertyFetch, $this->nodeFactory->createNull());
    }
    private function matchPropertyTypeClassReflection(PropertyFetch $propertyFetch) : ?ClassReflection
    {
        $propertyFetchVarType = $this->getType($propertyFetch->var);
        $className = ClassNameFromObjectTypeResolver::resolve($propertyFetchVarType);
        if ($className === null) {
            return null;
        }
        if ($className === 'stdClass') {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        return $this->reflectionProvider->getClass($className);
    }
}
