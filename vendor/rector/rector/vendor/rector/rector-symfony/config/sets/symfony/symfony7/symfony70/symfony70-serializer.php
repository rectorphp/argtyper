<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md#frameworkbundle
        'Argtyper202511\\Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer' => 'Argtyper202511\\Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface',
        'Argtyper202511\\Symfony\\Component\\Serializer\\Normalizer\\PropertyNormalizer' => 'Argtyper202511\\Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface',
    ]);
};
