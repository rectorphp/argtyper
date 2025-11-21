<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\ValueObject\MethodName;
final class MagicClassMethodAnalyzer
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
    public function isUnsafeOverridden(ClassMethod $classMethod) : bool
    {
        if ($this->nodeNameResolver->isName($classMethod, MethodName::INVOKE)) {
            return \false;
        }
        return $classMethod->isMagic();
    }
}
