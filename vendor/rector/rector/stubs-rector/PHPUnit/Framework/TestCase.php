<?php

declare (strict_types=1);
namespace Argtyper202511\PHPUnit\Framework;

use Argtyper202511\PHPUnit\Event\NoPreviousThrowableException;
use Argtyper202511\PHPUnit\Framework\MockObject\MockObject;
if (!\class_exists('Argtyper202511\\PHPUnit\\Framework\\TestCase')) {
    abstract class TestCase
    {
        /**
         * @psalm-template RealInstanceType of object
         *
         * @psalm-param class-string<RealInstanceType> $originalClassName
         *
         * @psalm-return MockObject&RealInstanceType
         */
        protected function createMock(string $originalClassName) : MockObject
        {
        }
    }
}
