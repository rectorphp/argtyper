<?php

declare(strict_types=1);

namespace Rector\ArgTyper\ValueObject;

use Rector\ArgTyper\Helpers\ProjectDirectoryFinder;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\ArgTyper\Tests\ValueObject\ProjectTest
 */
final readonly class Project
{
    public function __construct(
        private string $directory
    ) {
        Assert::directory($directory);

        // ensure vendor/autoload.php exists
        Assert::fileExists(
            $directory . '/vendor/autoload.php',
            'Could not find "vendor/autoload.php" in the project. Make sure its dependencies are installed'
        );

    }

    /**
     * @return string[]
     */
    public function getCodeDirectories(): array
    {
        $projectDirectoryFinder = new ProjectDirectoryFinder();
        return $projectDirectoryFinder->findCodeDirsRelative($this->directory);
    }

    /**
     * @return string[]
     */
    public function getAbsoluteCodeDirectories(): array
    {
        $projectDirectoryFinder = new ProjectDirectoryFinder();
        return $projectDirectoryFinder->findCodeDirsAbsolute($this->directory);
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }
}
