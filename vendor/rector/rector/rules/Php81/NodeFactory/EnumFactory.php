<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php81\NodeFactory;

use Argtyper202511\RectorPrefix202511\Nette\Utils\Strings;
use Argtyper202511\PhpParser\BuilderFactory;
use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassConst;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Enum_;
use Argtyper202511\PhpParser\Node\Stmt\EnumCase;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
final class EnumFactory
{
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
     * @var \PhpParser\BuilderFactory
     */
    private $builderFactory;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var string
     * @see https://stackoverflow.com/a/2560017
     * @see https://regex101.com/r/2xEQVj/1 for changing iso9001 to iso_9001
     * @see https://regex101.com/r/Ykm6ub/1 for changing XMLParser to XML_Parser
     * @see https://regex101.com/r/Zv4JhD/1 for changing needsReview to needs_Review
     */
    private const PASCAL_CASE_TO_UNDERSCORE_REGEX = '/(?<=[A-Z])(?=[A-Z][a-z])|(?<=[^A-Z])(?=[A-Z])|(?<=[A-Za-z])(?=[^A-Za-z])/';
    /**
     * @var string
     * @see https://regex101.com/r/FneU33/1
     */
    private const MULTI_UNDERSCORES_REGEX = '#_{2,}#';
    public function __construct(NodeNameResolver $nodeNameResolver, PhpDocInfoFactory $phpDocInfoFactory, BuilderFactory $builderFactory, ValueResolver $valueResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->builderFactory = $builderFactory;
        $this->valueResolver = $valueResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function createFromClass(Class_ $class) : Enum_
    {
        $shortClassName = $this->nodeNameResolver->getShortName($class);
        $enum = new Enum_($shortClassName, [], ['startLine' => $class->getStartLine(), 'endLine' => $class->getEndLine()]);
        $enum->namespacedName = $class->namespacedName;
        $constants = $class->getConstants();
        $enum->stmts = $class->getTraitUses();
        if ($constants !== []) {
            $value = $this->valueResolver->getValue($constants[0]->consts[0]->value);
            $enum->scalarType = \is_string($value) ? new Identifier('string') : new Identifier('int');
            // constant to cases
            foreach ($constants as $constant) {
                $enum->stmts[] = $this->createEnumCaseFromConst($constant);
            }
        }
        $enum->stmts = \array_merge($enum->stmts, $class->getMethods());
        return $enum;
    }
    public function createFromSpatieClass(Class_ $class, bool $enumNameInSnakeCase = \false) : Enum_
    {
        $shortClassName = $this->nodeNameResolver->getShortName($class);
        $enum = new Enum_($shortClassName, [], ['startLine' => $class->getStartLine(), 'endLine' => $class->getEndLine()]);
        $enum->namespacedName = $class->namespacedName;
        // constant to cases
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        $docBlockMethods = $phpDocInfo->getTagsByName('@method');
        if ($docBlockMethods !== []) {
            $mapping = $this->generateMappingFromClass($class);
            $identifierType = $this->getIdentifierTypeFromMappings($mapping);
            $enum->scalarType = new Identifier($identifierType);
            foreach ($docBlockMethods as $docBlockMethod) {
                $enum->stmts[] = $this->createEnumCaseFromDocComment($docBlockMethod, $class, $mapping, $enumNameInSnakeCase);
            }
        }
        return $enum;
    }
    private function createEnumCaseFromConst(ClassConst $classConst) : EnumCase
    {
        $constConst = $classConst->consts[0];
        $enumCase = new EnumCase($constConst->name, $constConst->value, [], ['startLine' => $constConst->getStartLine(), 'endLine' => $constConst->getEndLine()]);
        // mirror comments
        $enumCase->setAttribute(AttributeKey::PHP_DOC_INFO, $classConst->getAttribute(AttributeKey::PHP_DOC_INFO));
        $enumCase->setAttribute(AttributeKey::COMMENTS, $classConst->getAttribute(AttributeKey::COMMENTS));
        return $enumCase;
    }
    /**
     * @param array<int|string, mixed> $mapping
     */
    private function createEnumCaseFromDocComment(PhpDocTagNode $phpDocTagNode, Class_ $class, array $mapping = [], bool $enumNameInSnakeCase = \false) : EnumCase
    {
        /** @var MethodTagValueNode $nodeValue */
        $nodeValue = $phpDocTagNode->value;
        $enumValue = $mapping[$nodeValue->methodName] ?? $nodeValue->methodName;
        if ($enumNameInSnakeCase) {
            $enumName = \strtoupper(Strings::replace($nodeValue->methodName, self::PASCAL_CASE_TO_UNDERSCORE_REGEX, '_$0'));
            $enumName = Strings::replace($enumName, self::MULTI_UNDERSCORES_REGEX, '_');
        } else {
            $enumName = \strtoupper($nodeValue->methodName);
        }
        $enumExpr = $this->builderFactory->val($enumValue);
        return new EnumCase($enumName, $enumExpr, [], ['startLine' => $class->getStartLine(), 'endLine' => $class->getEndLine()]);
    }
    /**
     * @return array<int|string, mixed>
     */
    private function generateMappingFromClass(Class_ $class) : array
    {
        $classMethod = $class->getMethod('values');
        if (!$classMethod instanceof ClassMethod) {
            return [];
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($classMethod);
        /** @var array<int|string, mixed> $mapping */
        $mapping = [];
        foreach ($returns as $return) {
            if (!$return->expr instanceof Array_) {
                continue;
            }
            $mapping = $this->collectMappings($return->expr->items, $mapping);
        }
        return $mapping;
    }
    /**
     * @param null[]|ArrayItem[] $items
     * @param array<int|string, mixed> $mapping
     * @return array<int|string, mixed>
     */
    private function collectMappings(array $items, array $mapping) : array
    {
        foreach ($items as $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }
            if (!$item->key instanceof Int_ && !$item->key instanceof String_) {
                continue;
            }
            if (!$item->value instanceof Int_ && !$item->value instanceof String_) {
                continue;
            }
            $mapping[$item->key->value] = $item->value->value;
        }
        return $mapping;
    }
    /**
     * @param array<int|string, mixed> $mapping
     */
    private function getIdentifierTypeFromMappings(array $mapping) : string
    {
        $callableGetType = \Closure::fromCallable('gettype');
        $valueTypes = \array_map($callableGetType, $mapping);
        $uniqueValueTypes = \array_unique($valueTypes);
        if (\count($uniqueValueTypes) === 1) {
            $identifierType = \reset($uniqueValueTypes);
            if ($identifierType === 'integer') {
                $identifierType = 'int';
            }
        } else {
            $identifierType = 'string';
        }
        return $identifierType;
    }
}
