<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer;

use Argtyper202511\RectorPrefix202511\Nette\Utils\Strings;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
final class VariableInSprintfMaskMatcher
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, ValueResolver $valueResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->valueResolver = $valueResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    public function matchMask($functionLike, string $variableName, string $mask) : bool
    {
        if ($functionLike instanceof ArrowFunction) {
            $stmts = [$functionLike->expr];
        } else {
            $stmts = (array) $functionLike->stmts;
        }
        $funcCalls = $this->betterNodeFinder->findInstancesOfScoped($stmts, FuncCall::class);
        $funcCalls = \array_values(\array_filter($funcCalls, function (FuncCall $funcCall) : bool {
            return $this->nodeNameResolver->isName($funcCall->name, 'sprintf');
        }));
        if (\count($funcCalls) !== 1) {
            return \false;
        }
        $funcCall = $funcCalls[0];
        if ($funcCall->isFirstClassCallable()) {
            return \false;
        }
        $args = $funcCall->getArgs();
        if (\count($args) < 2) {
            return \false;
        }
        /** @var Arg $messageArg */
        $messageArg = \array_shift($args);
        $messageValue = $this->valueResolver->getValue($messageArg->value);
        if (!\is_string($messageValue)) {
            return \false;
        }
        // match all %s, %d types by position
        $masks = Strings::match($messageValue, '#%[sd]#');
        foreach ($args as $position => $arg) {
            if (!$arg->value instanceof Variable) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($arg->value, $variableName)) {
                continue;
            }
            if (!isset($masks[$position])) {
                continue;
            }
            $knownMaskOnPosition = $masks[$position];
            if ($knownMaskOnPosition !== $mask) {
                continue;
            }
            $type = $this->nodeTypeResolver->getNativeType($arg->value);
            if ($type instanceof MixedType && $type->getSubtractedType() instanceof UnionType) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
