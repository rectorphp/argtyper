<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\SideEffect;

use Argtyper202511\RectorPrefix202511\Nette\Utils\Strings;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\NullsafeMethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
final class SideEffectNodeDetector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\SideEffect\PureFunctionDetector
     */
    private $pureFunctionDetector;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var array<class-string<Expr>>
     */
    private const CALL_EXPR_SIDE_EFFECT_NODE_TYPES = [MethodCall::class, New_::class, NullsafeMethodCall::class, StaticCall::class];
    public function __construct(\Argtyper202511\Rector\DeadCode\SideEffect\PureFunctionDetector $pureFunctionDetector, BetterNodeFinder $betterNodeFinder, NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->pureFunctionDetector = $pureFunctionDetector;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function detect(Expr $expr): bool
    {
        if ($expr instanceof Assign) {
            return \true;
        }
        return (bool) $this->betterNodeFinder->findFirst($expr, \Closure::fromCallable([$this, 'detectCallExpr']));
    }
    public function detectCallExpr(Node $node): bool
    {
        if (!$node instanceof Expr) {
            return \false;
        }
        if ($node instanceof StaticCall && $this->isClassCallerThrowable($node)) {
            return \false;
        }
        if ($node instanceof New_ && $this->isPhpParser($node)) {
            return \false;
        }
        if (($node instanceof MethodCall || $node instanceof StaticCall) && $this->isTestMock($node)) {
            return \false;
        }
        $exprClass = get_class($node);
        if (in_array($exprClass, self::CALL_EXPR_SIDE_EFFECT_NODE_TYPES, \true)) {
            return \true;
        }
        if ($node instanceof FuncCall) {
            return !$this->pureFunctionDetector->detect($node);
        }
        if ($node instanceof Variable || $node instanceof ArrayDimFetch) {
            $variable = $this->resolveVariable($node);
            // variables don't have side effects
            return !$variable instanceof Variable;
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function isTestMock($node): bool
    {
        $objectType = new ObjectType('Argtyper202511\PHPUnit\Framework\TestCase');
        $nodeCaller = $node instanceof MethodCall ? $node->var : $node->class;
        if (!$this->nodeTypeResolver->isObjectType($nodeCaller, $objectType)) {
            return \false;
        }
        return $this->nodeNameResolver->isName($node->name, 'createMock');
    }
    private function isPhpParser(New_ $new): bool
    {
        if (!$new->class instanceof FullyQualified) {
            return \false;
        }
        $className = $new->class->toString();
        $namespace = Strings::before($className, '\\', 1);
        return $namespace === 'PhpParser';
    }
    private function isClassCallerThrowable(StaticCall $staticCall): bool
    {
        $class = $staticCall->class;
        if (!$class instanceof Name) {
            return \false;
        }
        $throwableType = new ObjectType('Throwable');
        $type = new ObjectType($class->toString());
        return $throwableType->isSuperTypeOf($type)->yes();
    }
    /**
     * @param \PhpParser\Node\Expr\ArrayDimFetch|\PhpParser\Node\Expr\Variable $expr
     */
    private function resolveVariable($expr): ?Variable
    {
        while ($expr instanceof ArrayDimFetch) {
            $expr = $expr->var;
        }
        if (!$expr instanceof Variable) {
            return null;
        }
        return $expr;
    }
}
