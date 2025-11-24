<?php

declare (strict_types=1);
namespace RectorPrefix202511\Doctrine\Inflector\Rules\Esperanto;

use RectorPrefix202511\Doctrine\Inflector\GenericLanguageInflectorFactory;
use RectorPrefix202511\Doctrine\Inflector\Rules\Ruleset;
final class InflectorFactory extends GenericLanguageInflectorFactory
{
    protected function getSingularRuleset(): Ruleset
    {
        return \RectorPrefix202511\Doctrine\Inflector\Rules\Esperanto\Rules::getSingularRuleset();
    }
    protected function getPluralRuleset(): Ruleset
    {
        return \RectorPrefix202511\Doctrine\Inflector\Rules\Esperanto\Rules::getPluralRuleset();
    }
}
