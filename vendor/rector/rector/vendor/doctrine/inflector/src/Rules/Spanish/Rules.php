<?php

declare (strict_types=1);
namespace RectorPrefix202512\Doctrine\Inflector\Rules\Spanish;

use RectorPrefix202512\Doctrine\Inflector\Rules\Patterns;
use RectorPrefix202512\Doctrine\Inflector\Rules\Ruleset;
use RectorPrefix202512\Doctrine\Inflector\Rules\Substitutions;
use RectorPrefix202512\Doctrine\Inflector\Rules\Transformations;
final class Rules
{
    public static function getSingularRuleset(): Ruleset
    {
        return new Ruleset(new Transformations(...\RectorPrefix202512\Doctrine\Inflector\Rules\Spanish\Inflectible::getSingular()), new Patterns(...\RectorPrefix202512\Doctrine\Inflector\Rules\Spanish\Uninflected::getSingular()), (new Substitutions(...\RectorPrefix202512\Doctrine\Inflector\Rules\Spanish\Inflectible::getIrregular()))->getFlippedSubstitutions());
    }
    public static function getPluralRuleset(): Ruleset
    {
        return new Ruleset(new Transformations(...\RectorPrefix202512\Doctrine\Inflector\Rules\Spanish\Inflectible::getPlural()), new Patterns(...\RectorPrefix202512\Doctrine\Inflector\Rules\Spanish\Uninflected::getPlural()), new Substitutions(...\RectorPrefix202512\Doctrine\Inflector\Rules\Spanish\Inflectible::getIrregular()));
    }
}
