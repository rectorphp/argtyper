<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeManipulator;

use Argtyper202511\RectorPrefix202511\Doctrine\ORM\Mapping\Table;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticPropertyFetch;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PhpParser\Node\Stmt\Trait_;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Enum\ClassName;
use Argtyper202511\Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeNestingScope\ContextAnalyzer;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Argtyper202511\Rector\Php80\NodeAnalyzer\PromotedPropertyResolver;
use Argtyper202511\Rector\PhpParser\AstResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\PhpParser\NodeFinder\PropertyFetchFinder;
use Argtyper202511\Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Argtyper202511\Rector\ValueObject\MethodName;
/**
 * For inspiration to improve this service,
 * @see examples of variable modifications in https://wiki.php.net/rfc/readonly_properties_v2#proposal
 */
final class PropertyManipulator
{
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
     * @var \Rector\PhpParser\NodeFinder\PropertyFetchFinder
     */
    private $propertyFetchFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PromotedPropertyResolver
     */
    private $promotedPropertyResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector
     */
    private $constructorAssignDetector;
    /**
     * @readonly
     * @var \Rector\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\ContextAnalyzer
     */
    private $contextAnalyzer;
    /**
     * @var string[]|class-string<Table>[]
     */
    private const ALLOWED_NOT_READONLY_CLASS_ANNOTATIONS = ['Argtyper202511\\ApiPlatform\\Core\\Annotation\\ApiResource', 'Argtyper202511\\ApiPlatform\\Metadata\\ApiResource', 'Argtyper202511\\Doctrine\\ORM\\Mapping\\Entity', 'Argtyper202511\\Doctrine\\ORM\\Mapping\\Table', 'Argtyper202511\\Doctrine\\ORM\\Mapping\\MappedSuperclass', 'Argtyper202511\\Doctrine\\ORM\\Mapping\\Embeddable', 'Argtyper202511\\Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\Document', 'Argtyper202511\\Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\EmbeddedDocument'];
    public function __construct(BetterNodeFinder $betterNodeFinder, PhpDocInfoFactory $phpDocInfoFactory, PropertyFetchFinder $propertyFetchFinder, NodeNameResolver $nodeNameResolver, PhpAttributeAnalyzer $phpAttributeAnalyzer, NodeTypeResolver $nodeTypeResolver, PromotedPropertyResolver $promotedPropertyResolver, ConstructorAssignDetector $constructorAssignDetector, AstResolver $astResolver, PropertyFetchAnalyzer $propertyFetchAnalyzer, ContextAnalyzer $contextAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->promotedPropertyResolver = $promotedPropertyResolver;
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->astResolver = $astResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->contextAnalyzer = $contextAnalyzer;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $propertyOrParam
     */
    public function isPropertyChangeableExceptConstructor(Class_ $class, $propertyOrParam, Scope $scope) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        if ($this->hasAllowedNotReadonlyAnnotationOrAttribute($phpDocInfo, $class)) {
            return \true;
        }
        if ($this->phpAttributeAnalyzer->hasPhpAttribute($propertyOrParam, ClassName::JMS_TYPE)) {
            return \true;
        }
        $propertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($class, $propertyOrParam, $scope);
        $classMethod = $class->getMethod(MethodName::CONSTRUCT);
        foreach ($propertyFetches as $propertyFetch) {
            if ($this->contextAnalyzer->isChangeableContext($propertyFetch)) {
                return \true;
            }
            // skip for constructor? it is allowed to set value in constructor method
            $propertyName = (string) $this->nodeNameResolver->getName($propertyFetch);
            if ($this->isPropertyAssignedOnlyInConstructor($class, $propertyName, $propertyFetch, $classMethod)) {
                continue;
            }
            if ($this->contextAnalyzer->isLeftPartOfAssign($propertyFetch)) {
                return \true;
            }
            if ($propertyFetch->getAttribute(AttributeKey::IS_UNSET_VAR) === \true) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @api Used in rector-symfony
     */
    public function resolveExistingClassPropertyNameByType(Class_ $class, ObjectType $objectType) : ?string
    {
        foreach ($class->getProperties() as $property) {
            $propertyType = $this->nodeTypeResolver->getType($property);
            if (!$propertyType->equals($objectType)) {
                continue;
            }
            return $this->nodeNameResolver->getName($property);
        }
        $promotedPropertyParams = $this->promotedPropertyResolver->resolveFromClass($class);
        foreach ($promotedPropertyParams as $promotedPropertyParam) {
            $paramType = $this->nodeTypeResolver->getType($promotedPropertyParam);
            if (!$paramType->equals($objectType)) {
                continue;
            }
            return $this->nodeNameResolver->getName($promotedPropertyParam);
        }
        return null;
    }
    public function isUsedByTrait(ClassReflection $classReflection, string $propertyName) : bool
    {
        foreach ($classReflection->getTraits() as $traitUse) {
            $trait = $this->astResolver->resolveClassFromClassReflection($traitUse);
            if (!$trait instanceof Trait_) {
                continue;
            }
            if ($this->propertyFetchAnalyzer->containsLocalPropertyFetchName($trait, $propertyName)) {
                return \true;
            }
        }
        return \false;
    }
    public function hasTraitWithSamePropertyOrWritten(ClassReflection $classReflection, string $propertyName) : bool
    {
        foreach ($classReflection->getTraits() as $traitUse) {
            if ($traitUse->hasInstanceProperty($propertyName) || $traitUse->hasStaticProperty($propertyName)) {
                return \true;
            }
            $trait = $this->astResolver->resolveClassFromClassReflection($traitUse);
            if (!$trait instanceof Trait_) {
                continue;
            }
            // is property written to
            if ($this->propertyFetchAnalyzer->containsWrittenPropertyFetchName($trait, $propertyName)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Expr\StaticPropertyFetch|\PhpParser\Node\Expr\PropertyFetch $propertyFetch
     */
    private function isPropertyAssignedOnlyInConstructor(Class_ $class, string $propertyName, $propertyFetch, ?ClassMethod $classMethod) : bool
    {
        if (!$classMethod instanceof ClassMethod) {
            return \false;
        }
        $node = $this->betterNodeFinder->findFirst((array) $classMethod->stmts, static function (Node $subNode) use($propertyFetch) : bool {
            return ($subNode instanceof PropertyFetch || $subNode instanceof StaticPropertyFetch) && $subNode === $propertyFetch;
        });
        // there is property unset in Test class, so only check on __construct
        if (!$node instanceof Node) {
            return \false;
        }
        return $this->constructorAssignDetector->isPropertyAssigned($class, $propertyName);
    }
    private function hasAllowedNotReadonlyAnnotationOrAttribute(PhpDocInfo $phpDocInfo, Class_ $class) : bool
    {
        if ($phpDocInfo->hasByAnnotationClasses(self::ALLOWED_NOT_READONLY_CLASS_ANNOTATIONS)) {
            return \true;
        }
        return $this->phpAttributeAnalyzer->hasPhpAttributes($class, self::ALLOWED_NOT_READONLY_CLASS_ANNOTATIONS);
    }
}
