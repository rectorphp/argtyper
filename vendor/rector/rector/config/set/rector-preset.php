<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Privatization\Rector\Class_\FinalizeTestCaseClassRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([DeclareStrictTypesRector::class, PostIncDecToPreIncDecRector::class, FinalizeTestCaseClassRector::class]);
};
