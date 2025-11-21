<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\AssignOp\Coalesce as AssignOpCoalesce;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Coalesce;
use Argtyper202511\PhpParser\Node\Expr\CallLike;
use Argtyper202511\PhpParser\Node\Expr\Cast\Array_;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\Empty_;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\FunctionLike;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Echo_;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Argtyper202511\Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\StrictArrayParamDimFetchRector\StrictArrayParamDimFetchRectorTest
 */
final class StrictArrayParamDimFetchRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, TypeComparator $typeComparator, TypeFactory $typeFactory)
    {
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->typeComparator = $typeComparator;
        $this->typeFactory = $typeFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add array type based on array dim fetch use', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function resolve($item)
    {
        return $item['name'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function resolve(array $item)
    {
        return $item['name'];
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = \false;
        if ($node instanceof ClassMethod && $this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($node)) {
            return null;
        }
        foreach ($node->getParams() as $param) {
            if ($param->type instanceof Node) {
                continue;
            }
            if ($param->variadic) {
                continue;
            }
            if ($param->default instanceof Expr && !$this->getType($param->default)->isArray()->yes()) {
                continue;
            }
            if (!$this->isParamAccessedArrayDimFetch($param, $node)) {
                continue;
            }
            $param->type = new Identifier('array');
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function isParamAccessedArrayDimFetch(Param $param, $functionLike): bool
    {
        if ($functionLike->stmts === null) {
            return \false;
        }
        $paramName = $this->getName($param);
        $isParamAccessedArrayDimFetch = \false;
        $this->traverseNodesWithCallable($functionLike->stmts, function (Node $node) use ($paramName, &$isParamAccessedArrayDimFetch): ?int {
            if ($node instanceof Class_ || $node instanceof FunctionLike) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($this->shouldStop($node, $paramName)) {
                // force set to false to avoid too early replaced
                $isParamAccessedArrayDimFetch = \false;
                return NodeVisitor::STOP_TRAVERSAL;
            }
            if (!$node instanceof ArrayDimFetch) {
                return null;
            }
            if (!$node->dim instanceof Expr) {
                return null;
            }
            if (!$node->var instanceof Variable) {
                return null;
            }
            if (!$this->isName($node->var, $paramName)) {
                return null;
            }
            // skip possible strings
            $variableType = $this->getType($node->var);
            if ($variableType->isString()->yes()) {
                // force set to false to avoid too early replaced
                $isParamAccessedArrayDimFetch = \false;
                return NodeVisitor::STOP_TRAVERSAL;
            }
            // skip integer in possibly string type as string can be accessed via int
            $dimType = $this->getType($node->dim);
            if ($dimType->isInteger()->yes() && $variableType->isString()->maybe()) {
                return null;
            }
            $variableType = $this->typeFactory->createMixedPassedOrUnionType([$variableType]);
            if ($variableType instanceof UnionType) {
                $isParamAccessedArrayDimFetch = \false;
                return NodeVisitor::STOP_TRAVERSAL;
            }
            if ($this->isArrayAccess($variableType)) {
                $isParamAccessedArrayDimFetch = \false;
                return NodeVisitor::STOP_TRAVERSAL;
            }
            $isParamAccessedArrayDimFetch = \true;
            return null;
        });
        return $isParamAccessedArrayDimFetch;
    }
    private function isEchoed(Node $node, string $paramName): bool
    {
        if (!$node instanceof Echo_) {
            return \false;
        }
        foreach ($node->exprs as $expr) {
            if ($expr instanceof Variable && $this->isName($expr, $paramName)) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldStop(Node $node, string $paramName): bool
    {
        $nodeToCheck = null;
        if ($node instanceof FuncCall && !$node->isFirstClassCallable() && $this->isNames($node, ['is_array', 'is_string', 'is_int', 'is_bool', 'is_float'])) {
            $firstArg = $node->getArgs()[0];
            $nodeToCheck = $firstArg->value;
        }
        if ($node instanceof Expression) {
            $nodeToCheck = $node->expr;
        }
        if ($node instanceof Coalesce) {
            $nodeToCheck = $node->left;
        }
        if ($node instanceof AssignOpCoalesce) {
            $nodeToCheck = $node->var;
        }
        if ($this->isMethodCall($paramName, $nodeToCheck)) {
            return \true;
        }
        if ($nodeToCheck instanceof Variable && $this->isName($nodeToCheck, $paramName)) {
            return \true;
        }
        if ($this->isEmptyOrEchoedOrCasted($node, $paramName)) {
            return \true;
        }
        return $this->isReassignAndUseAsArg($node, $paramName);
    }
    private function isReassignAndUseAsArg(Node $node, string $paramName): bool
    {
        if (!$node instanceof Assign) {
            return \false;
        }
        if (!$node->var instanceof Variable) {
            return \false;
        }
        if (!$this->isName($node->var, $paramName)) {
            return \false;
        }
        if (!$node->expr instanceof CallLike) {
            return \false;
        }
        if ($node->expr->isFirstClassCallable()) {
            return \false;
        }
        foreach ($node->expr->getArgs() as $arg) {
            if ($arg->value instanceof Variable && $this->isName($arg->value, $paramName)) {
                return \true;
            }
        }
        return \false;
    }
    private function isEmptyOrEchoedOrCasted(Node $node, string $paramName): bool
    {
        if ($node instanceof Empty_ && $node->expr instanceof Variable && $this->isName($node->expr, $paramName)) {
            return \true;
        }
        if ($this->isEchoed($node, $paramName)) {
            return \true;
        }
        return $node instanceof Array_ && $node->expr instanceof Variable && $this->isName($node->expr, $paramName);
    }
    private function isMethodCall(string $paramName, ?Node $node): bool
    {
        if ($node instanceof MethodCall) {
            return $node->var instanceof Variable && $this->isName($node->var, $paramName);
        }
        return \false;
    }
    private function isArrayAccess(Type $type): bool
    {
        if (!$type instanceof ObjectType) {
            return \false;
        }
        return $this->typeComparator->isSubtype($type, new ObjectType('ArrayAccess'));
    }
}
