<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
final class ArgsAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param Arg[] $args
     */
    public function hasNamedArg(array $args) : bool
    {
        foreach ($args as $arg) {
            if ($arg->name instanceof Identifier) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Arg[] $args
     */
    public function resolveArgPosition(array $args, string $name, int $defaultPosition) : int
    {
        foreach ($args as $position => $arg) {
            if (!$arg->name instanceof Identifier) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($arg->name, $name)) {
                continue;
            }
            return $position;
        }
        return $defaultPosition;
    }
}
