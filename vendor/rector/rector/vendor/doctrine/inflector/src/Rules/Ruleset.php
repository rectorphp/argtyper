<?php

declare (strict_types=1);
namespace RectorPrefix202512\Doctrine\Inflector\Rules;

class Ruleset
{
    /** @var Transformations */
    private $regular;
    /** @var Patterns */
    private $uninflected;
    /** @var Substitutions */
    private $irregular;
    public function __construct(\RectorPrefix202512\Doctrine\Inflector\Rules\Transformations $regular, \RectorPrefix202512\Doctrine\Inflector\Rules\Patterns $uninflected, \RectorPrefix202512\Doctrine\Inflector\Rules\Substitutions $irregular)
    {
        $this->regular = $regular;
        $this->uninflected = $uninflected;
        $this->irregular = $irregular;
    }
    public function getRegular(): \RectorPrefix202512\Doctrine\Inflector\Rules\Transformations
    {
        return $this->regular;
    }
    public function getUninflected(): \RectorPrefix202512\Doctrine\Inflector\Rules\Patterns
    {
        return $this->uninflected;
    }
    public function getIrregular(): \RectorPrefix202512\Doctrine\Inflector\Rules\Substitutions
    {
        return $this->irregular;
    }
}
