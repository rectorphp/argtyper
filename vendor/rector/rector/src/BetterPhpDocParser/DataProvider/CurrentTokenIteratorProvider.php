<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\BetterPhpDocParser\DataProvider;

use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
final class CurrentTokenIteratorProvider
{
    /**
     * @var \Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator|null
     */
    private $betterTokenIterator;
    public function setBetterTokenIterator(BetterTokenIterator $betterTokenIterator) : void
    {
        $this->betterTokenIterator = $betterTokenIterator;
    }
    public function provide() : BetterTokenIterator
    {
        if (!$this->betterTokenIterator instanceof BetterTokenIterator) {
            throw new ShouldNotHappenException();
        }
        return $this->betterTokenIterator;
    }
}
