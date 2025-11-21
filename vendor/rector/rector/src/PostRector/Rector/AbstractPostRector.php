<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PostRector\Rector;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\NodeVisitorAbstract;
use Argtyper202511\Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Argtyper202511\Rector\PostRector\Contract\Rector\PostRectorInterface;
use Argtyper202511\Rector\ValueObject\Application\File;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
abstract class AbstractPostRector extends NodeVisitorAbstract implements PostRectorInterface
{
    /**
     * @var \Rector\ValueObject\Application\File|null
     */
    private $file = null;
    /**
     * @param Stmt[] $stmts
     */
    public function shouldTraverse(array $stmts) : bool
    {
        return \true;
    }
    public function setFile(File $file) : void
    {
        $this->file = $file;
    }
    public function getFile() : File
    {
        Assert::isInstanceOf($this->file, File::class);
        return $this->file;
    }
    protected function addRectorClassWithLine(Node $node) : void
    {
        Assert::isInstanceOf($this->file, File::class);
        $rectorWithLineChange = new RectorWithLineChange(static::class, $node->getStartLine());
        $this->file->addRectorClassWithLine($rectorWithLineChange);
    }
}
