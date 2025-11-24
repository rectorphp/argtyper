<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Symfony\JMS\Rector\Class_\AccessTypeAnnotationToAttributeRector;
use Rector\Symfony\JMS\Rector\Property\AccessorAnnotationToAttributeRector;
/**
 * @see https://github.com/schmittjoh/serializer/pull/1320
 * @see https://github.com/schmittjoh/serializer/pull/1332
 * @see https://github.com/schmittjoh/serializer/pull/1337
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\AccessorOrder'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\Discriminator'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\Exclude'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\ExclusionPolicy'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\Expose'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\Groups'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\Inline'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\MaxDepth'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\PostDeserialize'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\PostSerialize'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\PreSerialize'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\ReadOnly'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\ReadOnlyProperty'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\SerializedName'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\Since'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\SkipWhenEmpty'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\Type'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\Until'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\VirtualProperty'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\XmlAttributeMap'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\XmlAttribute'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\XmlDiscriminator'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\XmlElement'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\XmlKeyValuePairs'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\XmlList'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\XmlMap'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\XmlNamespace'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\XmlRoot'), new AnnotationToAttribute('Argtyper202511\JMS\Serializer\Annotation\XmlValue')]);
    $rectorConfig->rules([AccessTypeAnnotationToAttributeRector::class, AccessorAnnotationToAttributeRector::class]);
};
