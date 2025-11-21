<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    // @see https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.4.md#jsonstreamer
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202511\Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'getNativeToStreamValueTransformer', 'getValueTransformers'), new MethodCallRename('Argtyper202511\Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'getStreamToNativeValueTransformers', 'getValueTransformers'), new MethodCallRename('Argtyper202511\Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'withNativeToStreamValueTransformers', 'withValueTransformers'), new MethodCallRename('Argtyper202511\Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'withStreamToNativeValueTransformers', 'withValueTransformers'), new MethodCallRename('Argtyper202511\Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'withAdditionalNativeToStreamValueTransformer', 'withAdditionalValueTransformer'), new MethodCallRename('Argtyper202511\Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'withAdditionalStreamToNativeValueTransformer', 'withAdditionalValueTransformer')]);
};
