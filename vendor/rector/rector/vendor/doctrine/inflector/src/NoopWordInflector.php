<?php

declare (strict_types=1);
namespace RectorPrefix202511\Doctrine\Inflector;

class NoopWordInflector implements \RectorPrefix202511\Doctrine\Inflector\WordInflector
{
    public function inflect(string $word): string
    {
        return $word;
    }
}
