<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Renaming\Contract;

use Argtyper202511\PHPStan\Type\ObjectType;
interface MethodCallRenameInterface
{
    public function getClass(): string;
    public function getObjectType(): ObjectType;
    public function getOldMethod(): string;
    public function getNewMethod(): string;
}
