<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Enum;

final class ResponseClass
{
    /**
     * @var string
     */
    public const REDIRECT = 'Argtyper202511\Symfony\Component\HttpFoundation\RedirectResponse';
    /**
     * @var string
     */
    public const BINARY_FILE = 'Argtyper202511\Symfony\Component\HttpFoundation\BinaryFileResponse';
    /**
     * @var string
     */
    public const JSON = 'Argtyper202511\Symfony\Component\HttpFoundation\JsonResponse';
    /**
     * @var string
     */
    public const STREAMED = 'Argtyper202511\Symfony\Component\HttpFoundation\StreamedResponse';
    /**
     * @var string
     */
    public const BASIC = 'Argtyper202511\Symfony\Component\HttpFoundation\Response';
}
