<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\VersionBonding;

use Argtyper202511\Rector\Contract\Rector\RectorInterface;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\Php\PolyfillPackagesProvider;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Rector\VersionBonding\Contract\RelatedPolyfillInterface;
final class PhpVersionedFilter
{
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\Php\PolyfillPackagesProvider
     */
    private $polyfillPackagesProvider;
    public function __construct(PhpVersionProvider $phpVersionProvider, PolyfillPackagesProvider $polyfillPackagesProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->polyfillPackagesProvider = $polyfillPackagesProvider;
    }
    /**
     * @param list<RectorInterface> $rectors
     * @return list<RectorInterface>
     */
    public function filter(array $rectors): array
    {
        $minProjectPhpVersion = $this->phpVersionProvider->provide();
        $activeRectors = [];
        foreach ($rectors as $rector) {
            if ($rector instanceof RelatedPolyfillInterface) {
                $polyfillPackageNames = $this->polyfillPackagesProvider->provide();
                if (in_array($rector->providePolyfillPackage(), $polyfillPackageNames, \true)) {
                    $activeRectors[] = $rector;
                    continue;
                }
            }
            if (!$rector instanceof MinPhpVersionInterface) {
                $activeRectors[] = $rector;
                continue;
            }
            // does satisfy version? → include
            if ($rector->provideMinPhpVersion() <= $minProjectPhpVersion) {
                $activeRectors[] = $rector;
            }
        }
        return $activeRectors;
    }
}
