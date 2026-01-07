<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Enum;

final class PHPUnitClassName
{
    /**
     * @var string
     */
    public const TEST_CASE = 'Argtyper202601\PHPUnit\Framework\TestCase';
    /**
     * @var string
     */
    public const TEST_CASE_LEGACY = 'PHPUnit_Framework_TestCase';
    /**
     * @var string
     */
    public const ASSERT = 'Argtyper202601\PHPUnit\Framework\Assert';
    /**
     * @var string
     */
    public const INVOCATION_ORDER = 'Argtyper202601\PHPUnit\Framework\MockObject\Rule\InvocationOrder';
    /**
     * @var string
     */
    public const TEST_LISTENER = 'Argtyper202601\PHPUnit\Framework\TestListener';
    /**
     * @var string[]
     */
    public const TEST_CLASSES = [self::TEST_CASE, self::TEST_CASE_LEGACY];
}
