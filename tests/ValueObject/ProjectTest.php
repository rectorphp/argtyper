<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\ValueObject;

use PHPUnit\Framework\TestCase;
use Rector\ArgTyper\ValueObject\Project;

final class ProjectTest extends TestCase
{
    public function testDirs(): void
    {
        $project = new Project(__DIR__ . '/Fixture');

        $this->assertSame(__DIR__ . '/Fixture', $project->getDirectory());

        $this->assertSame(['src', 'tests'], $project->getCodeDirectories());
        $this->assertSame(
            [__DIR__ . '/Fixture/src', __DIR__ . '/Fixture/tests'],
            $project->getAbsoluteCodeDirectories()
        );
    }

    public function testMessage(): void
    {
        $this->expectExceptionMessage('The path "non-existing-path" is not a directory');

        new Project('non-existing-path');
    }

    public function testMissingAutoload(): void
    {
        $this->expectExceptionMessage(
            'Could not find "vendor/autoload.php" in the project. Make sure its dependencies are installed'
        );

        new Project(__DIR__);
    }
}
