<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Arguments\Contract;

interface ReplaceArgumentDefaultValueInterface
{
    public function getPosition(): int;
    /**
     * @return mixed
     */
    public function getValueBefore();
    /**
     * @return mixed
     */
    public function getValueAfter();
}
