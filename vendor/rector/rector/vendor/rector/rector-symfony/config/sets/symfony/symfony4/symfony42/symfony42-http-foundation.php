<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Argtyper202511\Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Transform\Rector\New_\NewToStaticCallRector;
use Argtyper202511\Rector\Transform\ValueObject\NewToStaticCall;
use Argtyper202511\Rector\ValueObject\MethodName;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(NewToStaticCallRector::class, [new NewToStaticCall('Argtyper202511\\Symfony\\Component\\HttpFoundation\\Cookie', 'Argtyper202511\\Symfony\\Component\\HttpFoundation\\Cookie', 'create')]);
    // https://github.com/symfony/symfony/commit/9493cfd5f2366dab19bbdde0d0291d0575454567
    $rectorConfig->ruleWithConfiguration(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Argtyper202511\\Symfony\\Component\\HttpFoundation\\Cookie', MethodName::CONSTRUCT, 5, \false, null), new ReplaceArgumentDefaultValue('Argtyper202511\\Symfony\\Component\\HttpFoundation\\Cookie', MethodName::CONSTRUCT, 8, null, 'lax'), new ReplaceArgumentDefaultValue('Argtyper202511\\Symfony\\Component\\HttpFoundation\\Cookie', '__construct', 8, 'none', 'Argtyper202511\\Symfony\\Component\\HttpFoundation\\Cookie::SAMESITE_NONE'), new ReplaceArgumentDefaultValue('Argtyper202511\\Symfony\\Component\\HttpFoundation\\Cookie', 'create', 8, 'none', 'Argtyper202511\\Symfony\\Component\\HttpFoundation\\Cookie::SAMESITE_NONE'), new ReplaceArgumentDefaultValue('Argtyper202511\\Symfony\\Component\\HttpFoundation\\Cookie', 'create', 8, 'lax', 'Argtyper202511\\Symfony\\Component\\HttpFoundation\\Cookie::SAMESITE_LAX'), new ReplaceArgumentDefaultValue('Argtyper202511\\Symfony\\Component\\HttpFoundation\\Cookie', 'create', 8, 'strict', 'Argtyper202511\\Symfony\\Component\\HttpFoundation\\Cookie::SAMESITE_STRICT')]);
};
