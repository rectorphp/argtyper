<?php

declare (strict_types=1);
namespace Rector\Symfony\Enum;

final class SymfonyAttribute
{
    /**
     * @var string
     */
    public const AUTOWIRE = 'Argtyper202601\Symfony\Component\DependencyInjection\Attribute\Autowire';
    /**
     * @var string
     */
    public const AS_COMMAND = 'Argtyper202601\Symfony\Component\Console\Attribute\AsCommand';
    /**
     * @var string
     */
    public const COMMAND_OPTION = 'Argtyper202601\Symfony\Component\Console\Attribute\Option';
    /**
     * @var string
     */
    public const COMMAND_ARGUMENT = 'Argtyper202601\Symfony\Component\Console\Attribute\Argument';
    /**
     * @var string
     */
    public const AS_EVENT_LISTENER = 'Argtyper202601\Symfony\Component\EventDispatcher\Attribute\AsEventListener';
    /**
     * @var string
     */
    public const ROUTE = 'Argtyper202601\Symfony\Component\Routing\Attribute\Route';
    /**
     * @var string
     */
    public const IS_GRANTED = 'Argtyper202601\Symfony\Component\Security\Http\Attribute\IsGranted';
    /**
     * @var string
     */
    public const REQUIRED = 'Argtyper202601\Symfony\Contracts\Service\Attribute\Required';
}
