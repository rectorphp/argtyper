<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr\CallLike;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\PhpParser\Node\NodeFactory;
final class LiteralCallLikeConstFetchReplacer
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @template TCallLike as MethodCall|New_|StaticCall
     *
     * @param TCallLike $callLike
     * @param array<string|int, string> $constantMap
     * @return TCallLike
     */
    public function replaceArgOnPosition(CallLike $callLike, int $argPosition, string $className, array $constantMap): ?\Argtyper202511\PhpParser\Node\Expr\CallLike
    {
        $args = $callLike->getArgs();
        if (!isset($args[$argPosition])) {
            return null;
        }
        $arg = $args[$argPosition];
        if (!$arg->value instanceof String_ && !$arg->value instanceof Int_) {
            return null;
        }
        $scalar = $arg->value;
        $constantName = $constantMap[$scalar->value] ?? null;
        if ($constantName === null) {
            return null;
        }
        $arg->value = $this->nodeFactory->createClassConstFetch($className, $constantName);
        return $callLike;
    }
}
