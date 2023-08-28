<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes\Tests\Rector;

use PHPStan\Type\ArrayType;
use PHPStan\Type\StringType;
use TomasVotruba\SherlockTypes\Rector\RectorConfigPrinter;
use TomasVotruba\SherlockTypes\Tests\AbstractTestCase;
use TomasVotruba\SherlockTypes\ValueObject\ClassMethodType;

final class RectorConfigPrinterTest extends AbstractTestCase
{
    private RectorConfigPrinter $rectorConfigPrinter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rectorConfigPrinter = $this->make(RectorConfigPrinter::class);
    }

    public function test(): void
    {
        $classMethodTypes = [
            new ClassMethodType('SomeClass', 'someMethod', StringType::class),
            new ClassMethodType('SomeClass', 'anotherMethod', ArrayType::class),
        ];

        $printedContents = $this->rectorConfigPrinter->print($classMethodTypes);
        $this->assertStringEqualsFile(__DIR__ . '/Fixture/expected-generated-rector.php', $printedContents);
    }
}