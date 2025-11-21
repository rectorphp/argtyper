<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\NodeManipulator;

use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
final class ClassManipulator
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
     * @param string[] $interfaceFQNS
     */
    public function removeImplements(Class_ $class, array $interfaceFQNS): void
    {
        foreach ($class->implements as $key => $implement) {
            if (!$this->nodeNameResolver->isNames($implement, $interfaceFQNS)) {
                continue;
            }
            unset($class->implements[$key]);
        }
    }
}
