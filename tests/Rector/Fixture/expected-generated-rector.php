<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [
        new AddParamTypeDeclaration(\SomeClass::class, 'someMethod', 0, new PHPStan\Type\StringType()),
        new AddParamTypeDeclaration(\SomeClass::class, 'anotherMethod', 0, new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new PHPStan\Type\MixedType())),
    ]);
};
