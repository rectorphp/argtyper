<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\ChangesReporting\ValueObjectFactory;

use Argtyper202511\Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Argtyper202511\Rector\Console\Formatter\ColorConsoleDiffFormatter;
use Argtyper202511\Rector\Differ\DefaultDiffer;
use Argtyper202511\Rector\FileSystem\FilePathHelper;
use Argtyper202511\Rector\ValueObject\Application\File;
use Argtyper202511\Rector\ValueObject\Reporting\FileDiff;
final class FileDiffFactory
{
    /**
     * @readonly
     * @var \Rector\Differ\DefaultDiffer
     */
    private $defaultDiffer;
    /**
     * @readonly
     * @var \Rector\FileSystem\FilePathHelper
     */
    private $filePathHelper;
    /**
     * @readonly
     * @var \Rector\Console\Formatter\ColorConsoleDiffFormatter
     */
    private $colorConsoleDiffFormatter;
    public function __construct(DefaultDiffer $defaultDiffer, FilePathHelper $filePathHelper, ColorConsoleDiffFormatter $colorConsoleDiffFormatter)
    {
        $this->defaultDiffer = $defaultDiffer;
        $this->filePathHelper = $filePathHelper;
        $this->colorConsoleDiffFormatter = $colorConsoleDiffFormatter;
    }
    /**
     * @param RectorWithLineChange[] $rectorsWithLineChanges
     */
    public function createFileDiffWithLineChanges(bool $shouldShowDiffs, File $file, string $oldContent, string $newContent, array $rectorsWithLineChanges) : FileDiff
    {
        $relativeFilePath = $this->filePathHelper->relativePath($file->getFilePath());
        $diff = $shouldShowDiffs ? $this->defaultDiffer->diff($oldContent, $newContent) : '';
        $consoleDiff = $shouldShowDiffs ? $this->colorConsoleDiffFormatter->format($diff) : '';
        // always keep the most recent diff
        return new FileDiff($relativeFilePath, $diff, $consoleDiff, $rectorsWithLineChanges);
    }
}
