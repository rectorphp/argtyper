<?php

declare (strict_types=1);
namespace Argtyper202511\PhpParser\Node;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\NodeAbstract;
class StaticVar extends NodeAbstract
{
    /** @var Expr\Variable Variable */
    public $var;
    /** @var null|Node\Expr Default value */
    public $default;
    /**
     * Constructs a static variable node.
     *
     * @param Expr\Variable $var Name
     * @param null|Node\Expr $default Default value
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(\Argtyper202511\PhpParser\Node\Expr\Variable $var, ?Node\Expr $default = null, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->var = $var;
        $this->default = $default;
    }
    public function getSubNodeNames() : array
    {
        return ['var', 'default'];
    }
    public function getType() : string
    {
        return 'StaticVar';
    }
}
// @deprecated compatibility alias
\class_alias(\Argtyper202511\PhpParser\Node\StaticVar::class, \Argtyper202511\PhpParser\Node\Stmt\StaticVar::class);
