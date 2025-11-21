<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Configuration;

use Argtyper202511\Rector\Configuration\Parameter\SimpleParameterProvider;
use Argtyper202511\Rector\Skipper\FileSystem\PathNormalizer;
final class VendorMissAnalyseGuard
{
    /**
     * @param string[] $filePaths
     */
    public function isVendorAnalyzed(array $filePaths): bool
    {
        if ($this->hasDowngradeSets()) {
            return \false;
        }
        return $this->containsVendorPath($filePaths);
    }
    private function hasDowngradeSets(): bool
    {
        $registeredRectorSets = SimpleParameterProvider::provideArrayParameter(\Argtyper202511\Rector\Configuration\Option::REGISTERED_RECTOR_SETS);
        foreach ($registeredRectorSets as $registeredRectorSet) {
            if (strpos((string) $registeredRectorSet, 'downgrade-') !== \false) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param string[] $filePaths
     */
    private function containsVendorPath(array $filePaths): bool
    {
        $cwdLength = strlen(getcwd());
        foreach ($filePaths as $filePath) {
            $normalizedPath = PathNormalizer::normalize(realpath($filePath));
            if (strncmp((string) substr($normalizedPath, $cwdLength), '/vendor/', strlen('/vendor/')) === 0) {
                return \true;
            }
        }
        return \false;
    }
}
