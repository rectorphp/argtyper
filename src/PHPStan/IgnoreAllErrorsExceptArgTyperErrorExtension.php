<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan;

use PhpParser\Node;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\IgnoreErrorExtension;
use PHPStan\Analyser\Scope;

final class IgnoreAllErrorsExceptArgTyperErrorExtension implements IgnoreErrorExtension
{
    public function shouldIgnore(Error $error, Node $node, Scope $scope): bool
    {
		// Ignore all errors since ArgTyper only collects types and doesn't throw errors
        return true;
    }
}
