<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Skipper\Skipper;

use Argtyper202511\Rector\Skipper\Matcher\FileInfoMatcher;
use Argtyper202511\Rector\Skipper\SkipCriteriaResolver\SkippedPathsResolver;
final class PathSkipper
{
    /**
     * @readonly
     * @var \Rector\Skipper\Matcher\FileInfoMatcher
     */
    private $fileInfoMatcher;
    /**
     * @readonly
     * @var \Rector\Skipper\SkipCriteriaResolver\SkippedPathsResolver
     */
    private $skippedPathsResolver;
    public function __construct(FileInfoMatcher $fileInfoMatcher, SkippedPathsResolver $skippedPathsResolver)
    {
        $this->fileInfoMatcher = $fileInfoMatcher;
        $this->skippedPathsResolver = $skippedPathsResolver;
    }
    public function shouldSkip(string $filePath): bool
    {
        $skippedPaths = $this->skippedPathsResolver->resolve();
        return $this->fileInfoMatcher->doesFileInfoMatchPatterns($filePath, $skippedPaths);
    }
}
