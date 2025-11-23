<?php

declare (strict_types=1);
namespace Rector\Symfony\Enum;

final class SymfonyAttribute
{
    /**
     * @var string
     */
    public const AUTOWIRE = 'Argtyper202511\Symfony\Component\DependencyInjection\Attribute\Autowire';
    /**
     * @var string
     */
    public const AS_COMMAND = 'Argtyper202511\Symfony\Component\Console\Attribute\AsCommand';
    /**
     * @var string
     */
    public const COMMAND_OPTION = 'Argtyper202511\Symfony\Component\Console\Attribute\Option';
    /**
     * @var string
     */
    public const COMMAND_ARGUMENT = 'Argtyper202511\Symfony\Component\Console\Attribute\Argument';
    /**
     * @var string
     */
    public const AS_EVENT_LISTENER = 'Argtyper202511\Symfony\Component\EventDispatcher\Attribute\AsEventListener';
    /**
     * @var string
     */
    public const ROUTE = 'Argtyper202511\Symfony\Component\Routing\Attribute\Route';
    /**
     * @var string
     */
    public const IS_GRANTED = 'Argtyper202511\Symfony\Component\Security\Http\Attribute\IsGranted';
    /**
     * @var string
     */
    public const REQUIRED = 'Argtyper202511\Symfony\Contracts\Service\Attribute\Required';
}
