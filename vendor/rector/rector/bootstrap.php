<?php

declare (strict_types=1);
namespace Argtyper202511;

use Argtyper202511\PHPParser\Node;
use Argtyper202511\PHPUnit\Runner\Version;
/**
 * The preload.php contains 2 dependencies
 *      - phpstan/phpdoc-parser
 *      - nikic/php-parser
 *
 * They need to be loaded early to avoid conflict version between rector prefixed vendor and Project vendor
 * For example, a project may use phpstan/phpdoc-parser v1, while rector uses phpstan/phpdoc-parser uses v2, that will error as class or logic are different.
 */
if (\defined('PHPUNIT_COMPOSER_INSTALL') && !\interface_exists(Node::class, \false) && !\class_exists(Version::class, \false) && \class_exists(Version::class, \true) && (int) Version::id() >= 12) {
    require_once __DIR__ . '/preload.php';
}
// inspired by https://github.com/phpstan/phpstan/blob/master/bootstrap.php
\spl_autoload_register(function (string $class) : void {
    static $composerAutoloader;
    // already loaded in bin/rector.php
    if (\defined('__RECTOR_RUNNING__')) {
        return;
    }
    // load prefixed or native class, e.g. for running tests
    if (\strpos($class, 'RectorPrefix') === 0 || \strpos($class, 'Rector\\') === 0) {
        if ($composerAutoloader === null) {
            // prefixed version autoload
            $composerAutoloader = (require __DIR__ . '/vendor/autoload.php');
        }
        // some weird collision with PHPStan custom rule tests
        if (!\is_int($composerAutoloader)) {
            $composerAutoloader->loadClass($class);
        }
    }
});
