<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Skipper\FileSystem;

/**
 * @see \Rector\Tests\Skipper\FileSystem\FnMatchPathNormalizerTest
 */
final class FnMatchPathNormalizer
{
    public function normalizeForFnmatch(string $path): string
    {
        if (substr_compare($path, '*', -strlen('*')) === 0 || strncmp($path, '*', strlen('*')) === 0) {
            return '*' . trim($path, '*') . '*';
        }
        if (strpos($path, '..') !== \false) {
            $realPath = realpath($path);
            if ($realPath === \false) {
                return '';
            }
            return \Argtyper202511\Rector\Skipper\FileSystem\PathNormalizer::normalize($realPath);
        }
        return $path;
    }
}
