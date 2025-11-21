<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpAttribute\Contract;

use Argtyper202511\PhpParser\Node;
/**
 * @template T as mixed
 */
interface AnnotationToAttributeMapperInterface
{
    /**
     * @param mixed $value
     */
    public function isCandidate($value) : bool;
    /**
     * @param T $value
     */
    public function map($value) : Node;
}
