<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\Enum;

final class NonAssertNonStaticMethods
{
    /**
     * @var string[]
     */
    public const ALL = ['createMock', 'atLeast', 'atLeastOnce', 'once', 'never', 'any', 'exactly', 'atMost', 'throwException', 'expectException', 'expectExceptionMessage', 'expectExceptionCode', 'expectExceptionMessageMatches'];
}
