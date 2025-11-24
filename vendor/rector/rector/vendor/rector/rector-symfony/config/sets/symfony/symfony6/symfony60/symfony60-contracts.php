<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/39484
        'Argtyper202511\Symfony\Contracts\HttpClient\HttpClientInterface\RemoteJsonManifestVersionStrategy' => 'Argtyper202511\Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy',
    ]);
};
