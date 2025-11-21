<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\DowngradePhp80\Rector\ClassMethod\RemoveReturnTypeDeclarationFromCloneRector;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\ValueObject\PhpVersion;
use Argtyper202511\Rector\DowngradePhp80\Rector\ArrayDimFetch\DowngradeDereferenceableOperationRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeAbstractPrivateMethodInTraitRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeRecursiveDirectoryIteratorHasChildrenRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\FuncCall\DowngradeNumberFormatNoFourthArgRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrContainsRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrEndsWithRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrStartsWithRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\FuncCall\DowngradeSubstrFalsyRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\Instanceof_\DowngradeInstanceofStringableRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\Instanceof_\DowngradePhp80ResourceReturnToObjectRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\MethodCall\DowngradeReflectionClassGetConstantsFilterRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\MethodCall\DowngradeReflectionGetAttributesRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\MethodCall\DowngradeReflectionPropertyGetDefaultValueRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\Property\DowngradeMixedTypeTypedPropertyRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector;
use Argtyper202511\Rector\DowngradePhp80\Rector\StaticCall\DowngradePhpTokenRector;
use Argtyper202511\Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation;
use Argtyper202511\Rector\Removing\Rector\Class_\RemoveInterfacesRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_74);
    $rectorConfig->ruleWithConfiguration(RemoveInterfacesRector::class, [
        // @see https://wiki.php.net/rfc/stringable
        'Stringable',
    ]);
    $rectorConfig->ruleWithConfiguration(DowngradeAttributeToAnnotationRector::class, [
        // Symfony
        new DowngradeAttributeToAnnotation('Argtyper202511\Symfony\Contracts\Service\Attribute\Required', 'required'),
        // Nette
        new DowngradeAttributeToAnnotation('Argtyper202511\Nette\DI\Attributes\Inject', 'inject'),
        // Jetbrains\PhpStorm\Language under nette/utils
        new DowngradeAttributeToAnnotation('Argtyper202511\Jetbrains\PhpStorm\Language', 'language'),
    ]);
    $rectorConfig->rules([DowngradeNamedArgumentRector::class, DowngradeDereferenceableOperationRector::class, DowngradeUnionTypeTypedPropertyRector::class, DowngradeUnionTypeDeclarationRector::class, DowngradeMixedTypeDeclarationRector::class, DowngradeStaticTypeDeclarationRector::class, DowngradeAbstractPrivateMethodInTraitRector::class, DowngradePropertyPromotionRector::class, DowngradeNonCapturingCatchesRector::class, DowngradeStrContainsRector::class, DowngradeMatchToSwitchRector::class, DowngradeClassOnObjectToGetClassRector::class, DowngradeArbitraryExpressionsSupportRector::class, DowngradeNullsafeToTernaryOperatorRector::class, DowngradeTrailingCommasInParamUseRector::class, DowngradeStrStartsWithRector::class, DowngradeStrEndsWithRector::class, DowngradePhpTokenRector::class, DowngradeThrowExprRector::class, DowngradePhp80ResourceReturnToObjectRector::class, DowngradeReflectionGetAttributesRector::class, DowngradeRecursiveDirectoryIteratorHasChildrenRector::class, DowngradeReflectionPropertyGetDefaultValueRector::class, DowngradeReflectionClassGetConstantsFilterRector::class, DowngradeArrayFilterNullableCallbackRector::class, DowngradeNumberFormatNoFourthArgRector::class, DowngradeStringReturnTypeOnToStringRector::class, DowngradeMixedTypeTypedPropertyRector::class, RemoveReturnTypeDeclarationFromCloneRector::class, DowngradeEnumToConstantListClassRector::class, DowngradeInstanceofStringableRector::class, DowngradeSubstrFalsyRector::class]);
};
