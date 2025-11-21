<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511\Symplify\EasyParallel\Contract;

use JsonSerializable;
/**
 * @api
 */
interface SerializableInterface extends JsonSerializable
{
    /**
     * @param array<string, mixed> $json
     */
    public static function decode(array $json) : self;
}
