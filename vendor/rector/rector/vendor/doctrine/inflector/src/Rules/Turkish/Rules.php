<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511\Doctrine\Inflector\Rules\Turkish;

use Argtyper202511\RectorPrefix202511\Doctrine\Inflector\Rules\Patterns;
use Argtyper202511\RectorPrefix202511\Doctrine\Inflector\Rules\Ruleset;
use Argtyper202511\RectorPrefix202511\Doctrine\Inflector\Rules\Substitutions;
use Argtyper202511\RectorPrefix202511\Doctrine\Inflector\Rules\Transformations;
final class Rules
{
    public static function getSingularRuleset(): Ruleset
    {
        return new Ruleset(new Transformations(...Inflectible::getSingular()), new Patterns(...Uninflected::getSingular()), (new Substitutions(...Inflectible::getIrregular()))->getFlippedSubstitutions());
    }
    public static function getPluralRuleset(): Ruleset
    {
        return new Ruleset(new Transformations(...Inflectible::getPlural()), new Patterns(...Uninflected::getPlural()), new Substitutions(...Inflectible::getIrregular()));
    }
}
