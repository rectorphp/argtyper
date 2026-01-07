<?php

/*
 * This file is part of the Fidry CPUCounter Config package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace RectorPrefix202512\Fidry\CpuCoreCounter\Finder;

final class FinderRegistry
{
    /**
     * @return list<CpuCoreFinder> List of all the known finders with all their variants.
     */
    public static function getAllVariants(): array
    {
        return [new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\CpuInfoFinder(), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder(1), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\HwLogicalFinder(), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\HwPhysicalFinder(), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\LscpuLogicalFinder(), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\LscpuPhysicalFinder(), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\_NProcessorFinder(), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\NProcessorFinder(), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\NProcFinder(\true), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\NProcFinder(\false), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\NullCpuCoreFinder(), \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\SkipOnOSFamilyFinder::forWindows(new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder(1)), \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\OnlyOnOSFamilyFinder::forWindows(new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder(1)), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\OnlyInPowerShellFinder(new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\CmiCmdletLogicalFinder()), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\OnlyInPowerShellFinder(new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\CmiCmdletPhysicalFinder()), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\WindowsRegistryLogicalFinder(), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\WmicPhysicalFinder(), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\WmicLogicalFinder()];
    }
    /**
     * @return list<CpuCoreFinder>
     */
    public static function getDefaultLogicalFinders(): array
    {
        return [\RectorPrefix202512\Fidry\CpuCoreCounter\Finder\OnlyOnOSFamilyFinder::forWindows(new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\OnlyInPowerShellFinder(new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\CmiCmdletLogicalFinder())), \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\OnlyOnOSFamilyFinder::forWindows(new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\WindowsRegistryLogicalFinder()), \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\OnlyOnOSFamilyFinder::forWindows(new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\WmicLogicalFinder()), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\NProcFinder(), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\HwLogicalFinder(), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\_NProcessorFinder(), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\NProcessorFinder(), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\LscpuLogicalFinder(), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\CpuInfoFinder()];
    }
    /**
     * @return list<CpuCoreFinder>
     */
    public static function getDefaultPhysicalFinders(): array
    {
        return [\RectorPrefix202512\Fidry\CpuCoreCounter\Finder\OnlyOnOSFamilyFinder::forWindows(new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\OnlyInPowerShellFinder(new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\CmiCmdletPhysicalFinder())), \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\OnlyOnOSFamilyFinder::forWindows(new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\WmicPhysicalFinder()), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\HwPhysicalFinder(), new \RectorPrefix202512\Fidry\CpuCoreCounter\Finder\LscpuPhysicalFinder()];
    }
    private function __construct()
    {
    }
}
