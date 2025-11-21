<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\ValueObject\PhpVersion;
use Argtyper202511\Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector;
use Argtyper202511\Rector\DowngradePhp72\Rector\ConstFetch\DowngradePhp72JsonConstRector;
use Argtyper202511\Rector\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector;
use Argtyper202511\Rector\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector;
use Argtyper202511\Rector\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector;
use Argtyper202511\Rector\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_71);
    $rectorConfig->rules([DowngradeObjectTypeDeclarationRector::class, DowngradeParameterTypeWideningRector::class, DowngradePregUnmatchedAsNullConstantRector::class, DowngradeStreamIsattyRector::class, DowngradeJsonDecodeNullAssociativeArgRector::class, DowngradePhp72JsonConstRector::class]);
};
