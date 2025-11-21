<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\VersionBonding\Contract;

use Argtyper202511\Rector\ValueObject\PolyfillPackage;
/**
 * Can be implemented by @see \Rector\Contract\Rector\RectorInterface
 */
interface RelatedPolyfillInterface
{
    /**
     * @return PolyfillPackage::*
     */
    public function providePolyfillPackage(): string;
}
