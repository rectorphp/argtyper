<?php

declare (strict_types=1);
namespace RectorPrefix202511\Doctrine\Inflector;

use RectorPrefix202511\Doctrine\Inflector\Rules\Ruleset;
use function array_unshift;
abstract class GenericLanguageInflectorFactory implements \RectorPrefix202511\Doctrine\Inflector\LanguageInflectorFactory
{
    /** @var Ruleset[] */
    private $singularRulesets = [];
    /** @var Ruleset[] */
    private $pluralRulesets = [];
    final public function __construct()
    {
        $this->singularRulesets[] = $this->getSingularRuleset();
        $this->pluralRulesets[] = $this->getPluralRuleset();
    }
    final public function build(): \RectorPrefix202511\Doctrine\Inflector\Inflector
    {
        return new \RectorPrefix202511\Doctrine\Inflector\Inflector(new \RectorPrefix202511\Doctrine\Inflector\CachedWordInflector(new \RectorPrefix202511\Doctrine\Inflector\RulesetInflector(...$this->singularRulesets)), new \RectorPrefix202511\Doctrine\Inflector\CachedWordInflector(new \RectorPrefix202511\Doctrine\Inflector\RulesetInflector(...$this->pluralRulesets)));
    }
    final public function withSingularRules(?Ruleset $singularRules, bool $reset = \false): \RectorPrefix202511\Doctrine\Inflector\LanguageInflectorFactory
    {
        if ($reset) {
            $this->singularRulesets = [];
        }
        if ($singularRules instanceof Ruleset) {
            array_unshift($this->singularRulesets, $singularRules);
        }
        return $this;
    }
    final public function withPluralRules(?Ruleset $pluralRules, bool $reset = \false): \RectorPrefix202511\Doctrine\Inflector\LanguageInflectorFactory
    {
        if ($reset) {
            $this->pluralRulesets = [];
        }
        if ($pluralRules instanceof Ruleset) {
            array_unshift($this->pluralRulesets, $pluralRules);
        }
        return $this;
    }
    abstract protected function getSingularRuleset(): Ruleset;
    abstract protected function getPluralRuleset(): Ruleset;
}
