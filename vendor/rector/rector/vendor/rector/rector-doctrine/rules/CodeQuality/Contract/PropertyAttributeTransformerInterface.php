<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Contract;

use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
interface PropertyAttributeTransformerInterface
{
    /**
     * @return MappingClass::*
     */
    public function getClassName(): string;
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    public function transform(EntityMapping $entityMapping, $property): bool;
}
