<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes\Tests\PHPStan;

use TomasVotruba\SherlockTypes\Tests\AbstractTestCase;

final class ResultInfererTest extends AbstractTestCase
{
    // run PHPStan on test case file and extract types
    public function test()
    {
        $fixtureFilePaht = __DIR__ . '/Fixture/SomeTest.php';
        // create PHSPtan here too :)
        // get file analyser
        // run rule and ocllector
        // compare expected json
    }
}