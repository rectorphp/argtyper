<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Skipper\Skipper;

use Argtyper202511\Rector\Skipper\Matcher\FileInfoMatcher;
final class SkipSkipper
{
    /**
     * @readonly
     * @var \Rector\Skipper\Matcher\FileInfoMatcher
     */
    private $fileInfoMatcher;
    public function __construct(FileInfoMatcher $fileInfoMatcher)
    {
        $this->fileInfoMatcher = $fileInfoMatcher;
    }
    /**
     * @param array<string, string[]|null> $skippedClasses
     * @param object|string $checker
     */
    public function doesMatchSkip($checker, string $filePath, array $skippedClasses) : bool
    {
        foreach ($skippedClasses as $skippedClass => $skippedFiles) {
            if (!\is_a($checker, $skippedClass, \true)) {
                continue;
            }
            // skip everywhere
            if (!\is_array($skippedFiles)) {
                return \true;
            }
            if ($this->fileInfoMatcher->doesFileInfoMatchPatterns($filePath, $skippedFiles)) {
                return \true;
            }
        }
        return \false;
    }
}
