<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Argtyper202511\Rector\Php80\ValueObject\AnnotationToAttribute;
use Argtyper202511\Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\AnnotationWithValueToAttributeRector;
use Argtyper202511\Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\CoversAnnotationWithValueToAttributeRector;
use Argtyper202511\Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\RequiresAnnotationWithValueToAttributeRector;
use Argtyper202511\Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\TicketAnnotationToAttributeRector;
use Argtyper202511\Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DataProviderAnnotationToAttributeRector;
use Argtyper202511\Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DependsAnnotationWithValueToAttributeRector;
use Argtyper202511\Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\TestWithAnnotationToAttributeRector;
use Argtyper202511\Rector\PHPUnit\ValueObject\AnnotationWithValueToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        TicketAnnotationToAttributeRector::class,
        TestWithAnnotationToAttributeRector::class,
        DataProviderAnnotationToAttributeRector::class,
        CoversAnnotationWithValueToAttributeRector::class,
        RequiresAnnotationWithValueToAttributeRector::class,
        /**
         * Currently handle:
         *      - @depends Methodname
         *      - @depends Classname::class
         *      - @depends clone MethodName
         *
         * Todo:
         *      - @depends Class::MethodName
         *      - @depends !clone, shallowClone, !shallowClone
         */
        DependsAnnotationWithValueToAttributeRector::class,
    ]);
    $rectorConfig->ruleWithConfiguration(AnnotationWithValueToAttributeRector::class, [new AnnotationWithValueToAttribute('backupGlobals', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\BackupGlobals', ['enabled' => \true, 'disabled' => \false]), new AnnotationWithValueToAttribute('backupStaticAttributes', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\BackupStaticProperties', ['enabled' => \true, 'disabled' => \false]), new AnnotationWithValueToAttribute('preserveGlobalState', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\PreserveGlobalState', ['enabled' => \true, 'disabled' => \false]), new AnnotationWithValueToAttribute('depends', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\Depends'), new AnnotationWithValueToAttribute('group', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\Group'), new AnnotationWithValueToAttribute('uses', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\UsesClass', [], \true), new AnnotationWithValueToAttribute('testDox', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\TestDox'), new AnnotationWithValueToAttribute('testdox', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\TestDox')]);
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        // @see https://github.com/sebastianbergmann/phpunit/issues/4502
        new AnnotationToAttribute('after', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\After'),
        new AnnotationToAttribute('afterClass', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\AfterClass'),
        new AnnotationToAttribute('before', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\Before'),
        new AnnotationToAttribute('beforeClass', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\BeforeClass'),
        // CodeCoverageIgnore attribute is deprecated.
        // @see https://github.com/rectorphp/rector-phpunit/pull/304
        // new AnnotationToAttribute('codeCoverageIgnore', 'PHPUnit\Framework\Attributes\CodeCoverageIgnore'),
        new AnnotationToAttribute('coversNothing', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\CoversNothing'),
        new AnnotationToAttribute('doesNotPerformAssertions', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\DoesNotPerformAssertions'),
        new AnnotationToAttribute('large', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\Large'),
        new AnnotationToAttribute('medium', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\Medium'),
        new AnnotationToAttribute('preCondition', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\PostCondition'),
        new AnnotationToAttribute('postCondition', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\PreCondition'),
        new AnnotationToAttribute('runInSeparateProcess', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\RunInSeparateProcess'),
        new AnnotationToAttribute('runTestsInSeparateProcesses', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\RunTestsInSeparateProcesses'),
        new AnnotationToAttribute('small', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\Small'),
        new AnnotationToAttribute('test', 'Argtyper202511\\PHPUnit\\Framework\\Attributes\\Test'),
    ]);
};
