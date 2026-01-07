<?php

declare (strict_types=1);
namespace RectorPrefix202512\Doctrine\Inflector\Rules\Italian;

use RectorPrefix202512\Doctrine\Inflector\Rules\Patterns;
use RectorPrefix202512\Doctrine\Inflector\Rules\Ruleset;
use RectorPrefix202512\Doctrine\Inflector\Rules\Substitutions;
use RectorPrefix202512\Doctrine\Inflector\Rules\Transformations;
final class Rules
{
    public static function getSingularRuleset(): Ruleset
    {
        return new Ruleset(new Transformations(...\RectorPrefix202512\Doctrine\Inflector\Rules\Italian\Inflectible::getSingular()), new Patterns(...\RectorPrefix202512\Doctrine\Inflector\Rules\Italian\Uninflected::getSingular()), (new Substitutions(...\RectorPrefix202512\Doctrine\Inflector\Rules\Italian\Inflectible::getIrregular()))->getFlippedSubstitutions());
    }
    public static function getPluralRuleset(): Ruleset
    {
        return new Ruleset(new Transformations(...\RectorPrefix202512\Doctrine\Inflector\Rules\Italian\Inflectible::getPlural()), new Patterns(...\RectorPrefix202512\Doctrine\Inflector\Rules\Italian\Uninflected::getPlural()), new Substitutions(...\RectorPrefix202512\Doctrine\Inflector\Rules\Italian\Inflectible::getIrregular()));
    }
}
