<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\NodeFactory;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\PhpParser\Node\NodeFactory;
use Argtyper202511\Rector\Symfony\ValueObject\EventNameToClassAndConstant;
final class EventReferenceFactory
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(NodeFactory $nodeFactory, ReflectionProvider $reflectionProvider)
    {
        $this->nodeFactory = $nodeFactory;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param EventNameToClassAndConstant[] $eventNamesToClassConstants
     * @return String_|ClassConstFetch
     */
    public function createEventName(string $eventName, array $eventNamesToClassConstants) : Node
    {
        if ($this->reflectionProvider->hasClass($eventName)) {
            return $this->nodeFactory->createClassConstReference($eventName);
        }
        // is string a that could be caught in constant, e.g. KernelEvents?
        foreach ($eventNamesToClassConstants as $eventNameToClassConstant) {
            if ($eventNameToClassConstant->getEventName() !== $eventName) {
                continue;
            }
            return $this->nodeFactory->createClassConstFetch($eventNameToClassConstant->getEventClass(), $eventNameToClassConstant->getEventConstant());
        }
        return new String_($eventName);
    }
}
