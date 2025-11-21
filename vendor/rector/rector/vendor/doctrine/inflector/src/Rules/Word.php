<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511\Doctrine\Inflector\Rules;

class Word
{
    /** @var string */
    private $word;
    public function __construct(string $word)
    {
        $this->word = $word;
    }
    public function getWord(): string
    {
        return $this->word;
    }
}
