<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\ValueObject;

use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\Rector\Symfony\Contract\EventReferenceToMethodNameInterface;
final class EventReferenceToMethodName implements EventReferenceToMethodNameInterface
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\ClassConstFetch
     */
    private $classConstFetch;
    /**
     * @readonly
     * @var string
     */
    private $methodName;
    public function __construct(ClassConstFetch $classConstFetch, string $methodName)
    {
        $this->classConstFetch = $classConstFetch;
        $this->methodName = $methodName;
    }
    public function getClassConstFetch(): ClassConstFetch
    {
        return $this->classConstFetch;
    }
    public function getMethodName(): string
    {
        return $this->methodName;
    }
}
