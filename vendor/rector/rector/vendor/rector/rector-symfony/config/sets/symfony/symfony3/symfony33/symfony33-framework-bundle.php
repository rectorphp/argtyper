<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # framework bundle
        'Argtyper202511\Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddConsoleCommandPass' => 'Argtyper202511\Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass',
        'Argtyper202511\Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\SerializerPass' => 'Argtyper202511\Symfony\Component\Serializer\DependencyInjection\SerializerPass',
        'Argtyper202511\Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\FormPass' => 'Argtyper202511\Symfony\Component\Form\DependencyInjection\FormPass',
        'Argtyper202511\Symfony\Bundle\FrameworkBundle\EventListener\SessionListener' => 'Argtyper202511\Symfony\Component\HttpKernel\EventListener\SessionListener',
        'Argtyper202511\Symfony\Bundle\FrameworkBundle\EventListener\TestSessionListener' => 'Argtyper202511\Symfony\Component\HttpKernel\EventListener\TestSessionListener',
        'Argtyper202511\Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\ConfigCachePass' => 'Argtyper202511\Symfony\Component\Config\DependencyInjection\ConfigCachePass',
        'Argtyper202511\Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\PropertyInfoPass' => 'Argtyper202511\Symfony\Component\PropertyInfo\DependencyInjection\PropertyInfoPass',
    ]);
};
