<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Contract\DependencyInjection;

interface ResetableInterface
{
    public function reset(): void;
}
