<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes\Tests;

use PHPUnit\Framework\TestCase;
use TomasVotruba\SherlockTypes\DependencyInjection\ContainerFactory;
use Webmozart\Assert\Assert;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @template TType as object
     * @param class-string<TType> $type
     * @return TType
     */
    protected function make(string $type): object
    {
        $containerFactory = new ContainerFactory();
        $container = $containerFactory->create();

        $service = $container->make($type);
        Assert::isInstanceOf($service, $type);

        return $service;
    }
}
