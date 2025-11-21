<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Set\Contract;

interface SetProviderInterface
{
    /**
     * @return SetInterface[]
     */
    public function provide() : array;
}
