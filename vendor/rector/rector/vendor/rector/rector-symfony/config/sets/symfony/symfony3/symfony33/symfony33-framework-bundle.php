<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # framework bundle
        'Argtyper202601\Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddConsoleCommandPass' => 'Argtyper202601\Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass',
        'Argtyper202601\Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\SerializerPass' => 'Argtyper202601\Symfony\Component\Serializer\DependencyInjection\SerializerPass',
        'Argtyper202601\Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\FormPass' => 'Argtyper202601\Symfony\Component\Form\DependencyInjection\FormPass',
        'Argtyper202601\Symfony\Bundle\FrameworkBundle\EventListener\SessionListener' => 'Argtyper202601\Symfony\Component\HttpKernel\EventListener\SessionListener',
        'Argtyper202601\Symfony\Bundle\FrameworkBundle\EventListener\TestSessionListener' => 'Argtyper202601\Symfony\Component\HttpKernel\EventListener\TestSessionListener',
        'Argtyper202601\Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\ConfigCachePass' => 'Argtyper202601\Symfony\Component\Config\DependencyInjection\ConfigCachePass',
        'Argtyper202601\Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\PropertyInfoPass' => 'Argtyper202601\Symfony\Component\PropertyInfo\DependencyInjection\PropertyInfoPass',
    ]);
};
