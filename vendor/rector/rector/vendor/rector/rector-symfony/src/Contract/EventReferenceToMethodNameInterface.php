<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Contract;

use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
interface EventReferenceToMethodNameInterface
{
    public function getClassConstFetch() : ClassConstFetch;
    public function getMethodName() : string;
}
