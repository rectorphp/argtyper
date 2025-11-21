<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Renaming\Contract;

interface RenameAnnotationInterface
{
    public function getOldAnnotation(): string;
    public function getNewAnnotation(): string;
}
