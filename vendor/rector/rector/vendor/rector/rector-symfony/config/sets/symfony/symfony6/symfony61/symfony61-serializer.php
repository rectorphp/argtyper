<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/43982
        'Argtyper202511\Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface' => 'Argtyper202511\Symfony\Component\Serializer\Normalizer\DenormalizerInterface',
        'Argtyper202511\Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface' => 'Argtyper202511\Symfony\Component\Serializer\Normalizer\NormalizerInterface',
    ]);
};
