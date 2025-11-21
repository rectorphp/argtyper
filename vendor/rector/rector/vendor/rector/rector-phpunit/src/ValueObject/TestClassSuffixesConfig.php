<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\ValueObject;

final class TestClassSuffixesConfig
{
    /**
     * @var string[]
     * @readonly
     */
    private $suffixes = ['Test', 'TestCase'];
    /**
     * @param string[] $suffixes
     */
    public function __construct(array $suffixes = ['Test', 'TestCase'])
    {
        $this->suffixes = $suffixes;
    }
    /**
     * @return string[]
     */
    public function getSuffixes(): array
    {
        return $this->suffixes;
    }
}
