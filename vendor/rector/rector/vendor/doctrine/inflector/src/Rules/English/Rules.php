<?php

declare (strict_types=1);
namespace RectorPrefix202512\Doctrine\Inflector\Rules\English;

use RectorPrefix202512\Doctrine\Inflector\Rules\Patterns;
use RectorPrefix202512\Doctrine\Inflector\Rules\Ruleset;
use RectorPrefix202512\Doctrine\Inflector\Rules\Substitutions;
use RectorPrefix202512\Doctrine\Inflector\Rules\Transformations;
final class Rules
{
    public static function getSingularRuleset(): Ruleset
    {
        return new Ruleset(new Transformations(...\RectorPrefix202512\Doctrine\Inflector\Rules\English\Inflectible::getSingular()), new Patterns(...\RectorPrefix202512\Doctrine\Inflector\Rules\English\Uninflected::getSingular()), (new Substitutions(...\RectorPrefix202512\Doctrine\Inflector\Rules\English\Inflectible::getIrregular()))->getFlippedSubstitutions());
    }
    public static function getPluralRuleset(): Ruleset
    {
        return new Ruleset(new Transformations(...\RectorPrefix202512\Doctrine\Inflector\Rules\English\Inflectible::getPlural()), new Patterns(...\RectorPrefix202512\Doctrine\Inflector\Rules\English\Uninflected::getPlural()), new Substitutions(...\RectorPrefix202512\Doctrine\Inflector\Rules\English\Inflectible::getIrregular()));
    }
}
