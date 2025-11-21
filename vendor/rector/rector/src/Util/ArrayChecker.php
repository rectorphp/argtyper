<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Util;

final class ArrayChecker
{
    /**
     * @param mixed[] $elements
     * @param callable(mixed $element): bool $callable
     */
    public function doesExist(array $elements, callable $callable) : bool
    {
        foreach ($elements as $element) {
            $isFound = $callable($element);
            if ($isFound) {
                return \true;
            }
        }
        return \false;
    }
}
