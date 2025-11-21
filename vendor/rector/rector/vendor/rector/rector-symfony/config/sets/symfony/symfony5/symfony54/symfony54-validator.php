<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Argtyper202511\Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    // @see https://symfony.com/blog/new-in-symfony-5-4-nested-validation-attributes
    // @see https://github.com/symfony/symfony/pull/41994
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [new AnnotationToAttribute('Argtyper202511\\Symfony\\Component\\Validator\\Constraints\\All'), new AnnotationToAttribute('Argtyper202511\\Symfony\\Component\\Validator\\Constraints\\Collection'), new AnnotationToAttribute('Argtyper202511\\Symfony\\Component\\Validator\\Constraints\\AtLeastOneOf'), new AnnotationToAttribute('Argtyper202511\\Symfony\\Component\\Validator\\Constraints\\Sequentially')]);
};
