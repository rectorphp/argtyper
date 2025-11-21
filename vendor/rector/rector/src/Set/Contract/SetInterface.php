<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Set\Contract;

interface SetInterface
{
    public function getGroupName(): string;
    public function getName(): string;
    public function getSetFilePath(): string;
}
