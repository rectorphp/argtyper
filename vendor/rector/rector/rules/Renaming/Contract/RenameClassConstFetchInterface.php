<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Renaming\Contract;

use Argtyper202511\PHPStan\Type\ObjectType;
interface RenameClassConstFetchInterface
{
    public function getOldObjectType(): ObjectType;
    public function getOldConstant(): string;
    public function getNewConstant(): string;
}
