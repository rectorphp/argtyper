<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Configuration;

use PHPStan\Reflection\ClassReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;

final class ProjectAutoloadGuard
{
    public function ensureProjectAutoloadFileIsLoaded(\PHPStan\Type\Type $callerType): void
    {
        if (! $callerType instanceof ObjectType) {
            return;
        }

        // call reflection is loaded properly
        if ($callerType->getClassReflection() instanceof ClassReflection) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Class reflection for "%s" class not found. Make sure you included the project autoload.%svendor/bin/phpstan ... --autoload-file=project/vendor/autoload.php',
            PHP_EOL,
            $callerType->getClassName()
        ));
    }
}
