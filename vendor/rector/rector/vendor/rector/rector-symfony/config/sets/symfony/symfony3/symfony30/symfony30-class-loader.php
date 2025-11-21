<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202511\Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader', 'registerNamespaces', 'addPrefixes'), new MethodCallRename('Argtyper202511\Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader', 'registerPrefixes', 'addPrefixes'), new MethodCallRename('Argtyper202511\Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader', 'registerNamespace', 'addPrefix'), new MethodCallRename('Argtyper202511\Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader', 'registerPrefix', 'addPrefix'), new MethodCallRename('Argtyper202511\Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader', 'getNamespaces', 'getPrefixes'), new MethodCallRename('Argtyper202511\Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader', 'getNamespaceFallbacks', 'getFallbackDirs'), new MethodCallRename('Argtyper202511\Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader', 'getPrefixFallbacks', 'getFallbackDirs')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Argtyper202511\Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader' => 'Argtyper202511\Symfony\Component\ClassLoader\ClassLoader']);
};
