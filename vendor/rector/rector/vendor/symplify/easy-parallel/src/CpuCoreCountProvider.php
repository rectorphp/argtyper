<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511\Symplify\EasyParallel;

use Argtyper202511\RectorPrefix202511\Fidry\CpuCoreCounter\CpuCoreCounter;
use Argtyper202511\RectorPrefix202511\Fidry\CpuCoreCounter\NumberOfCpuCoreNotFound;
/**
 * @api
 */
final class CpuCoreCountProvider
{
    /**
     * @var int
     */
    private const DEFAULT_CORE_COUNT = 2;
    public function provide(): int
    {
        try {
            return (new CpuCoreCounter())->getCount();
        } catch (NumberOfCpuCoreNotFound $exception) {
            return self::DEFAULT_CORE_COUNT;
        }
    }
}
