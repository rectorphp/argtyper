<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // MimeType
        'Argtyper202511\Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface' => 'Argtyper202511\Symfony\Component\Mime\MimeTypesInterface',
        'Argtyper202511\Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface' => 'Argtyper202511\Symfony\Component\Mime\MimeTypesInterface',
        'Argtyper202511\Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser' => 'Argtyper202511\Symfony\Component\Mime\MimeTypes',
        'Argtyper202511\Symfony\Component\HttpFoundation\File\MimeType\FileBinaryMimeTypeGuesser' => 'Argtyper202511\Symfony\Component\Mime\FileBinaryMimeTypeGuesser',
        'Argtyper202511\Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser' => 'Argtyper202511\Symfony\Component\Mime\FileinfoMimeTypeGuesser',
    ]);
};
