<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp81\Rector\FunctionLike;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\ComplexType;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\AssignOp\Coalesce as AssignCoalesce;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Coalesce;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\Error;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\FunctionLike;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\IntersectionType;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\NullableType;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PhpParser\Node\UnionType;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Php72\NodeFactory\AnonymousFunctionFactory;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/new_in_initializers
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\DowngradeNewInInitializerRectorTest
 */
final class DowngradeNewInInitializerRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Php72\NodeFactory\AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(AnonymousFunctionFactory $anonymousFunctionFactory, BetterNodeFinder $betterNodeFinder)
    {
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace New in initializers', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private Logger $logger = new NullLogger,
    ) {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private ?Logger $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger;
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
        return [FunctionLike::class];
    }
    /**
     * @param FunctionLike $node
     */
    public function refactor(Node $node) : ?FunctionLike
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var ClassMethod|Closure|Function_ $node */
        $node = $this->convertArrowFunctionToClosure($node);
        return $this->replaceNewInParams($node);
    }
    private function shouldSkip(FunctionLike $functionLike) : bool
    {
        foreach ($functionLike->getParams() as $param) {
            if ($this->isParamSkipped($param)) {
                continue;
            }
            return \false;
        }
        return \true;
    }
    private function convertArrowFunctionToClosure(FunctionLike $functionLike) : FunctionLike
    {
        if (!$functionLike instanceof ArrowFunction) {
            return $functionLike;
        }
        $stmts = [new Return_($functionLike->expr)];
        return $this->anonymousFunctionFactory->create($functionLike->params, $stmts, $functionLike->returnType, $functionLike->static);
    }
    private function isParamSkipped(Param $param) : bool
    {
        if ($param->var instanceof Error) {
            return \true;
        }
        if (!$param->default instanceof Expr) {
            return \true;
        }
        $hasNew = (bool) $this->betterNodeFinder->findFirstInstanceOf($param->default, New_::class);
        if (!$hasNew) {
            return \true;
        }
        return $param->type instanceof IntersectionType;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_ $functionLike
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_
     */
    private function replaceNewInParams($functionLike)
    {
        $isConstructor = $functionLike instanceof ClassMethod && $this->isName($functionLike, MethodName::CONSTRUCT);
        $stmts = [];
        foreach ($functionLike->getParams() as $param) {
            if ($this->isParamSkipped($param)) {
                continue;
            }
            /** @var Expr $default */
            $default = $param->default;
            /** @var Variable $paramVar */
            $paramVar = $param->var;
            // check for property promotion
            if ($isConstructor && $param->flags > 0) {
                $propertyFetch = new PropertyFetch(new Variable('this'), $paramVar->name);
                $coalesce = new Coalesce($param->var, $default);
                $assign = new Assign($propertyFetch, $coalesce);
                if ($param->type !== null) {
                    $param->type = $this->ensureNullableType($param->type);
                }
            } else {
                $assign = new AssignCoalesce($param->var, $default);
            }
            // recheck after
            if ($isConstructor && $param->type !== null) {
                $param->type = $this->ensureNullableType($param->type);
            }
            $stmts[] = new Expression($assign);
            $param->default = $this->nodeFactory->createNull();
        }
        if ($functionLike->stmts === null) {
            return $functionLike;
        }
        $functionLike->stmts = $functionLike->stmts ?? [];
        $functionLike->stmts = \array_merge($stmts, $functionLike->stmts);
        return $functionLike;
    }
    /**
     * @param \PhpParser\Node\Name|\PhpParser\Node\Identifier|\PhpParser\Node\ComplexType $type
     * @return \PhpParser\Node\NullableType|\PhpParser\Node\UnionType
     */
    private function ensureNullableType($type)
    {
        if ($type instanceof NullableType) {
            return $type;
        }
        if (!$type instanceof ComplexType) {
            return new NullableType($type);
        }
        if ($type instanceof UnionType) {
            foreach ($type->types as $typeChild) {
                if (!$typeChild instanceof Identifier) {
                    continue;
                }
                if ($typeChild->toLowerString() === 'null') {
                    return $type;
                }
            }
            $type->types[] = new Identifier('null');
            return $type;
        }
        throw new ShouldNotHappenException();
    }
}
