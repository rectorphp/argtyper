<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Application\Provider;

use Argtyper202511\Rector\ValueObject\Application\File;
/**
 * @internal Avoid this services if possible, pass File value object or file path directly
 */
final class CurrentFileProvider
{
    /**
     * @var \Rector\ValueObject\Application\File|null
     */
    private $file;
    public function setFile(File $file) : void
    {
        $this->file = $file;
    }
    public function getFile() : ?File
    {
        return $this->file;
    }
}
