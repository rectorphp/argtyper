<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Configuration\Parameter;

use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\Rector\Configuration\Option;
/**
 * Class to manage feature flags,
 * that loosen or tighten the behavior of Rector rules.
 */
final class FeatureFlags
{
    public static function treatClassesAsFinal(Class_ $class) : bool
    {
        // abstract class never can be treated as "final"
        // as always must be overridden
        if ($class->isAbstract()) {
            return \false;
        }
        return \Argtyper202511\Rector\Configuration\Parameter\SimpleParameterProvider::provideBoolParameter(Option::TREAT_CLASSES_AS_FINAL);
    }
}
