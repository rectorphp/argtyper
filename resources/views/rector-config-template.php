<?php

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [
        __CONFIGURATION__
    ]);
};
