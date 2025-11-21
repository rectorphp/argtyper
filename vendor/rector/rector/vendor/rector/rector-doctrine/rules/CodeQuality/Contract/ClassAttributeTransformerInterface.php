<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality\Contract;

use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Argtyper202511\Rector\Doctrine\Enum\MappingClass;
interface ClassAttributeTransformerInterface
{
    /**
     * @return MappingClass::*
     */
    public function getClassName() : string;
    public function transform(EntityMapping $entityMapping, Class_ $class) : bool;
}
