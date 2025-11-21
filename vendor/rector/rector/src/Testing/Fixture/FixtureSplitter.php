<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Testing\Fixture;

use Argtyper202511\RectorPrefix202511\Nette\Utils\FileSystem;
/**
 * @api
 */
final class FixtureSplitter
{
    public static function containsSplit(string $fixtureFileContent) : bool
    {
        return \strpos($fixtureFileContent, "-----\n") !== \false || \strpos($fixtureFileContent, "-----\r\n") !== \false;
    }
    /**
     * @return array<int, string>
     */
    public static function split(string $filePath) : array
    {
        $fixtureFileContents = FileSystem::read($filePath);
        return self::splitFixtureFileContents($fixtureFileContents);
    }
    /**
     * @return array<int, string>
     */
    public static function splitFixtureFileContents(string $fixtureFileContents) : array
    {
        $fixtureFileContents = \str_replace("\r\n", "\n", $fixtureFileContents);
        return \explode("-----\n", $fixtureFileContents);
    }
}
