<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector;
use Argtyper202511\Rector\DowngradePhp81\Rector\FuncCall\DowngradeHashAlgorithmXxHashRector;
use Argtyper202511\Rector\DowngradePhp81\Rector\LNumber\DowngradeOctalNumberRector;
use Argtyper202511\Rector\DowngradePhp81\Rector\MethodCall\DowngradeIsEnumRector;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\ValueObject\PhpVersion;
use Argtyper202511\Rector\DowngradePhp81\Rector\ClassConst\DowngradeFinalizePublicClassConstantRector;
use Argtyper202511\Rector\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector;
use Argtyper202511\Rector\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector;
use Argtyper202511\Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector;
use Argtyper202511\Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector;
use Argtyper202511\Rector\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector;
use Argtyper202511\Rector\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector;
use Argtyper202511\Rector\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector;
use Argtyper202511\Rector\DowngradePhp81\Rector\StmtsAwareInterface\DowngradeSetAccessibleReflectionPropertyRector;
use Argtyper202511\Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_80);
    $rectorConfig->rules([DowngradeFinalizePublicClassConstantRector::class, DowngradeFirstClassCallableSyntaxRector::class, DowngradeNeverTypeDeclarationRector::class, DowngradePureIntersectionTypeRector::class, DowngradeNewInInitializerRector::class, DowngradePhp81ResourceReturnToObjectRector::class, DowngradeReadonlyPropertyRector::class, DowngradeArraySpreadRector::class, DowngradeArrayIsListRector::class, DowngradeSetAccessibleReflectionPropertyRector::class, DowngradeIsEnumRector::class, DowngradeOctalNumberRector::class, DowngradeHashAlgorithmXxHashRector::class]);
    // @see https://php.watch/versions/8.1/internal-method-return-types#reflection
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('ReflectionFunction', 'hasTentativeReturnType', 'hasReturnType'), new MethodCallRename('ReflectionFunction', 'getTentativeReturnType', 'getReturnType'), new MethodCallRename('ReflectionMethod', 'hasTentativeReturnType', 'hasReturnType'), new MethodCallRename('ReflectionMethod', 'getTentativeReturnType', 'getReturnType')]);
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        // @see https://php.watch/versions/8.1/enums#enum-exists
        'enum_exists' => 'class_exists',
    ]);
};
