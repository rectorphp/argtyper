<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\VersionBonding\Contract;

use Argtyper202511\Rector\ValueObject\PhpVersion;
/**
 * Can be implemented by @see \Rector\Contract\Rector\RectorInterface
 *
 * Rules that do not meet this PHP version will be skipped.
 */
interface MinPhpVersionInterface
{
    /**
     * @return PhpVersion::*
     */
    public function provideMinPhpVersion(): int;
}
