<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/39484
        'Argtyper202511\\Symfony\\Contracts\\HttpClient\\HttpClientInterface\\RemoteJsonManifestVersionStrategy' => 'Argtyper202511\\Symfony\\Component\\Asset\\VersionStrategy\\JsonManifestVersionStrategy',
    ]);
};
