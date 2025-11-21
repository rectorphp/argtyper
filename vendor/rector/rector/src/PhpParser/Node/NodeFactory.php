<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpParser\Node;

use Argtyper202511\PhpParser\Builder\Method;
use Argtyper202511\PhpParser\Builder\Param as ParamBuilder;
use Argtyper202511\PhpParser\Builder\Property as PropertyBuilder;
use Argtyper202511\PhpParser\BuilderFactory;
use Argtyper202511\PhpParser\BuilderHelpers;
use Argtyper202511\PhpParser\Modifiers;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\DeclareItem;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanOr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Concat;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Argtyper202511\PhpParser\Node\Expr\Cast;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\Clone_;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Instanceof_;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\NullsafeMethodCall;
use Argtyper202511\PhpParser\Node\Expr\NullsafePropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Expr\StaticPropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\UnaryMinus;
use Argtyper202511\PhpParser\Node\Expr\UnaryPlus;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Scalar;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Declare_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Enum\ObjectReference;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\NodeDecorator\PropertyTypeDecorator;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\PostRector\ValueObject\PropertyMetadata;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
/**
 * @see \Rector\Tests\PhpParser\Node\NodeFactoryTest
 */
final class NodeFactory
{
    /**
     * @readonly
     * @var \PhpParser\BuilderFactory
     */
    private $builderFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\NodeDecorator\PropertyTypeDecorator
     */
    private $propertyTypeDecorator;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @var string
     */
    private const THIS = 'this';
    public function __construct(BuilderFactory $builderFactory, PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper, PropertyTypeDecorator $propertyTypeDecorator, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, PhpVersionProvider $phpVersionProvider)
    {
        $this->builderFactory = $builderFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->propertyTypeDecorator = $propertyTypeDecorator;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    /**
     * @param string|ObjectReference::* $className
     * Creates "\SomeClass::CONSTANT"
     */
    public function createClassConstFetch(string $className, string $constantName) : ClassConstFetch
    {
        $name = $this->createName($className);
        return $this->createClassConstFetchFromName($name, $constantName);
    }
    /**
     * @param string|ObjectReference::* $className
     * Creates "\SomeClass::class"
     */
    public function createClassConstReference(string $className) : ClassConstFetch
    {
        return $this->createClassConstFetch($className, 'class');
    }
    /**
     * Creates "['item', $variable]"
     *
     * @param mixed[] $items
     */
    public function createArray(array $items) : Array_
    {
        $arrayItems = [];
        $defaultKey = 0;
        foreach ($items as $key => $item) {
            $customKey = $key !== $defaultKey ? $key : null;
            $arrayItems[] = $this->createArrayItem($item, $customKey);
            ++$defaultKey;
        }
        return new Array_($arrayItems);
    }
    /**
     * Creates "($args)"
     *
     * @param mixed[] $values
     * @return Arg[]
     */
    public function createArgs(array $values) : array
    {
        foreach ($values as $key => $value) {
            if ($value instanceof ArrayItem) {
                $values[$key] = $value->value;
            }
        }
        return $this->builderFactory->args($values);
    }
    public function createDeclaresStrictType() : Declare_
    {
        $declareItem = new DeclareItem(new Identifier('strict_types'), new Int_(1));
        return new Declare_([$declareItem]);
    }
    /**
     * Creates $this->property = $property;
     */
    public function createPropertyAssignment(string $propertyName) : Assign
    {
        $variable = new Variable($propertyName);
        return $this->createPropertyAssignmentWithExpr($propertyName, $variable);
    }
    /**
     * @api
     */
    public function createPropertyAssignmentWithExpr(string $propertyName, Expr $expr) : Assign
    {
        $propertyFetch = $this->createPropertyFetch(self::THIS, $propertyName);
        return new Assign($propertyFetch, $expr);
    }
    /**
     * @param mixed $argument
     */
    public function createArg($argument) : Arg
    {
        return new Arg(BuilderHelpers::normalizeValue($argument));
    }
    public function createPublicMethod(string $name) : ClassMethod
    {
        $method = new Method($name);
        $method->makePublic();
        return $method->getNode();
    }
    public function createParamFromNameAndType(string $name, ?Type $type) : Param
    {
        $param = new ParamBuilder($name);
        if ($type instanceof Type) {
            $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PARAM);
            if ($typeNode instanceof Node) {
                $param->setType($typeNode);
            }
        }
        return $param->getNode();
    }
    public function createPrivatePropertyFromNameAndType(string $name, ?Type $type) : Property
    {
        $propertyBuilder = new PropertyBuilder($name);
        $propertyBuilder->makePrivate();
        $property = $propertyBuilder->getNode();
        $this->propertyTypeDecorator->decorate($property, $type);
        return $property;
    }
    /**
     * @api symfony
     * @param mixed[] $arguments
     */
    public function createLocalMethodCall(string $method, array $arguments = []) : MethodCall
    {
        $variable = new Variable('this');
        return $this->createMethodCall($variable, $method, $arguments);
    }
    /**
     * @param mixed[] $arguments
     * @param \PhpParser\Node\Expr|string $exprOrVariableName
     */
    public function createMethodCall($exprOrVariableName, string $method, array $arguments = []) : MethodCall
    {
        $callerExpr = $this->createMethodCaller($exprOrVariableName);
        return $this->builderFactory->methodCall($callerExpr, $method, $arguments);
    }
    /**
     * @param string|\PhpParser\Node\Expr $variableNameOrExpr
     */
    public function createPropertyFetch($variableNameOrExpr, string $property) : PropertyFetch
    {
        $fetcherExpr = \is_string($variableNameOrExpr) ? new Variable($variableNameOrExpr) : $variableNameOrExpr;
        return $this->builderFactory->propertyFetch($fetcherExpr, $property);
    }
    /**
     * @api doctrine
     */
    public function createPrivateProperty(string $name) : Property
    {
        $propertyBuilder = new PropertyBuilder($name);
        $propertyBuilder->makePrivate();
        $property = $propertyBuilder->getNode();
        $this->phpDocInfoFactory->createFromNode($property);
        return $property;
    }
    /**
     * @param Expr[] $exprs
     */
    public function createConcat(array $exprs) : ?Concat
    {
        if (\count($exprs) < 2) {
            return null;
        }
        $previousConcat = \array_shift($exprs);
        foreach ($exprs as $expr) {
            $previousConcat = new Concat($previousConcat, $expr);
        }
        if (!$previousConcat instanceof Concat) {
            throw new ShouldNotHappenException();
        }
        return $previousConcat;
    }
    /**
     * @param string|ObjectReference::* $class
     * @param Node[] $args
     */
    public function createStaticCall(string $class, string $method, array $args = []) : StaticCall
    {
        $name = $this->createName($class);
        $args = $this->createArgs($args);
        return new StaticCall($name, $method, $args);
    }
    /**
     * @param mixed[] $arguments
     */
    public function createFuncCall(string $name, array $arguments = []) : FuncCall
    {
        $arguments = $this->createArgs($arguments);
        return new FuncCall(new Name($name), $arguments);
    }
    public function createSelfFetchConstant(string $constantName) : ClassConstFetch
    {
        $name = new Name(ObjectReference::SELF);
        return new ClassConstFetch($name, $constantName);
    }
    public function createNull() : ConstFetch
    {
        return new ConstFetch(new Name('null'));
    }
    public function createPromotedPropertyParam(PropertyMetadata $propertyMetadata) : Param
    {
        $paramBuilder = new ParamBuilder($propertyMetadata->getName());
        $propertyType = $propertyMetadata->getType();
        if ($propertyType instanceof Type) {
            $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
            if ($typeNode instanceof Node) {
                $paramBuilder->setType($typeNode);
            }
        }
        $param = $paramBuilder->getNode();
        $propertyFlags = $propertyMetadata->getFlags();
        $param->flags = $propertyFlags !== 0 ? $propertyFlags : Modifiers::PRIVATE;
        // make readonly by default
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::READONLY_PROPERTY)) {
            $param->flags |= Modifiers::READONLY;
        }
        return $param;
    }
    public function createFalse() : ConstFetch
    {
        return new ConstFetch(new Name('false'));
    }
    public function createTrue() : ConstFetch
    {
        return new ConstFetch(new Name('true'));
    }
    /**
     * @api phpunit
     * @param string|ObjectReference::* $constantName
     */
    public function createClassConstFetchFromName(Name $className, string $constantName) : ClassConstFetch
    {
        return $this->builderFactory->classConstFetch($className, $constantName);
    }
    /**
     * @param array<NotIdentical|BooleanAnd|BooleanOr|Identical> $newNodes
     */
    public function createReturnBooleanAnd(array $newNodes) : ?Expr
    {
        if ($newNodes === []) {
            return null;
        }
        if (\count($newNodes) === 1) {
            return $newNodes[0];
        }
        return $this->createBooleanAndFromNodes($newNodes);
    }
    /**
     * Setting all child nodes to null is needed to avoid reprint of invalid tokens
     * @see https://github.com/rectorphp/rector/issues/8712
     *
     * @template TNode as Node
     *
     * @param TNode $node
     * @return TNode
     */
    public function createReprintedNode(Node $node) : Node
    {
        // reset original node, to allow the printer to re-use the node
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($node, static function (Node $subNode) : Node {
            $subNode->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            return $subNode;
        });
        return $node;
    }
    /**
     * @param string|int|null $key
     * @param mixed $item
     */
    private function createArrayItem($item, $key = null) : ArrayItem
    {
        $arrayItem = null;
        if ($item instanceof Variable || $item instanceof MethodCall || $item instanceof StaticCall || $item instanceof FuncCall || $item instanceof Concat || $item instanceof Scalar || $item instanceof Cast || $item instanceof ConstFetch || $item instanceof PropertyFetch || $item instanceof StaticPropertyFetch || $item instanceof NullsafePropertyFetch || $item instanceof NullsafeMethodCall || $item instanceof Clone_ || $item instanceof Instanceof_) {
            $arrayItem = new ArrayItem($item);
        } elseif ($item instanceof Identifier) {
            $string = new String_($item->toString());
            $arrayItem = new ArrayItem($string);
        } elseif (\is_scalar($item) || $item instanceof Array_) {
            $itemValue = BuilderHelpers::normalizeValue($item);
            $arrayItem = new ArrayItem($itemValue);
        } elseif (\is_array($item)) {
            $arrayItem = new ArrayItem($this->createArray($item));
        } elseif ($item === null || $item instanceof ClassConstFetch) {
            $itemValue = BuilderHelpers::normalizeValue($item);
            $arrayItem = new ArrayItem($itemValue);
        } elseif ($item instanceof Arg) {
            $arrayItem = new ArrayItem($item->value);
        }
        if ($arrayItem instanceof ArrayItem) {
            $this->decorateArrayItemWithKey($key, $arrayItem);
            return $arrayItem;
        }
        if ($item instanceof New_) {
            $arrayItem = new ArrayItem($item);
            $this->decorateArrayItemWithKey($key, $arrayItem);
            return $arrayItem;
        }
        if ($item instanceof UnaryPlus || $item instanceof UnaryMinus) {
            $arrayItem = new ArrayItem($item);
            $this->decorateArrayItemWithKey($key, $arrayItem);
            return $arrayItem;
        }
        // fallback to other nodes
        if ($item instanceof Expr) {
            $arrayItem = new ArrayItem($item);
            $this->decorateArrayItemWithKey($key, $arrayItem);
            return $arrayItem;
        }
        $itemValue = BuilderHelpers::normalizeValue($item);
        $arrayItem = new ArrayItem($itemValue);
        $this->decorateArrayItemWithKey($key, $arrayItem);
        return $arrayItem;
    }
    /**
     * @param int|string|null $key
     */
    private function decorateArrayItemWithKey($key, ArrayItem $arrayItem) : void
    {
        if ($key === null) {
            return;
        }
        $arrayItem->key = BuilderHelpers::normalizeValue($key);
    }
    /**
     * @param Expr\BinaryOp[] $binaryOps
     */
    private function createBooleanAndFromNodes(array $binaryOps) : BooleanAnd
    {
        /** @var NotIdentical|BooleanAnd $mainBooleanAnd */
        $mainBooleanAnd = \array_shift($binaryOps);
        foreach ($binaryOps as $binaryOp) {
            $mainBooleanAnd = new BooleanAnd($mainBooleanAnd, $binaryOp);
        }
        /** @var BooleanAnd $mainBooleanAnd */
        return $mainBooleanAnd;
    }
    /**
     * @param string|ObjectReference::* $className
     * @return \PhpParser\Node\Name|\PhpParser\Node\Name\FullyQualified
     */
    private function createName(string $className)
    {
        if (\in_array($className, [ObjectReference::PARENT, ObjectReference::SELF, ObjectReference::STATIC], \true)) {
            return new Name($className);
        }
        return new FullyQualified($className);
    }
    /**
     * @param \PhpParser\Node\Expr|string $exprOrVariableName
     * @return \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\Variable|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticPropertyFetch|\PhpParser\Node\Expr
     */
    private function createMethodCaller($exprOrVariableName)
    {
        if (\is_string($exprOrVariableName)) {
            return new Variable($exprOrVariableName);
        }
        if ($exprOrVariableName instanceof PropertyFetch) {
            return new PropertyFetch($exprOrVariableName->var, $exprOrVariableName->name);
        }
        if ($exprOrVariableName instanceof StaticPropertyFetch) {
            return new StaticPropertyFetch($exprOrVariableName->class, $exprOrVariableName->name);
        }
        if ($exprOrVariableName instanceof MethodCall) {
            return new MethodCall($exprOrVariableName->var, $exprOrVariableName->name, $exprOrVariableName->args);
        }
        return $exprOrVariableName;
    }
}
