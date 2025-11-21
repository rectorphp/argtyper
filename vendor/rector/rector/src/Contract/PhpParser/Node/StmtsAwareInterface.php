<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Contract\PhpParser\Node;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt;
/**
 * @property Stmt[]|null $stmts
 */
interface StmtsAwareInterface extends Node
{
}
