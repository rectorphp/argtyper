<?php

declare (strict_types=1);
namespace RectorPrefix202511\Doctrine\Inflector\Rules\Turkish;

use RectorPrefix202511\Doctrine\Inflector\Rules\Patterns;
use RectorPrefix202511\Doctrine\Inflector\Rules\Ruleset;
use RectorPrefix202511\Doctrine\Inflector\Rules\Substitutions;
use RectorPrefix202511\Doctrine\Inflector\Rules\Transformations;
final class Rules
{
    public static function getSingularRuleset(): Ruleset
    {
        return new Ruleset(new Transformations(...\RectorPrefix202511\Doctrine\Inflector\Rules\Turkish\Inflectible::getSingular()), new Patterns(...\RectorPrefix202511\Doctrine\Inflector\Rules\Turkish\Uninflected::getSingular()), (new Substitutions(...\RectorPrefix202511\Doctrine\Inflector\Rules\Turkish\Inflectible::getIrregular()))->getFlippedSubstitutions());
    }
    public static function getPluralRuleset(): Ruleset
    {
        return new Ruleset(new Transformations(...\RectorPrefix202511\Doctrine\Inflector\Rules\Turkish\Inflectible::getPlural()), new Patterns(...\RectorPrefix202511\Doctrine\Inflector\Rules\Turkish\Uninflected::getPlural()), new Substitutions(...\RectorPrefix202511\Doctrine\Inflector\Rules\Turkish\Inflectible::getIrregular()));
    }
}
