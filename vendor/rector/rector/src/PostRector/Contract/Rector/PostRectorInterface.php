<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PostRector\Contract\Rector;

use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\Rector\ValueObject\Application\File;
/**
 * @internal
 */
interface PostRectorInterface extends NodeVisitor
{
    /**
     * @param Stmt[] $stmts
     */
    public function shouldTraverse(array $stmts): bool;
    public function setFile(File $file): void;
}
