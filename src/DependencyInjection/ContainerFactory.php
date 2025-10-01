<?php

declare(strict_types=1);

namespace Rector\ArgTyper\DependencyInjection;

use Illuminate\Container\Container;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Rector\ArgTyper\Command\GenerateRectorConfigCommand;
use Rector\ArgTyper\Helpers\PrivatesAccessor;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ContainerFactory
{
    /**
     * @api
     */
    public function create(): Container
    {
        $container = new Container();

        $container->singleton(Parser::class, static function (): Parser {
            $parserFactory = new ParserFactory();
            return $parserFactory->createForHostVersion();
        });

        $container->singleton(
            SymfonyStyle::class,
            static function (): SymfonyStyle {
                // use null output ofr tests to avoid printing
                $consoleOutput = defined('PHPUNIT_COMPOSER_INSTALL') ? new NullOutput() : new ConsoleOutput();
                return new SymfonyStyle(new ArrayInput([]), $consoleOutput);
            }
        );

        $container->singleton(Application::class, function (Container $container): Application {
            $application = new Application();

            $generateRectorConfigCommand = $container->make(GenerateRectorConfigCommand::class);
            $application->add($generateRectorConfigCommand);

            $this->cleanupDefaultCommands($application);

            return $application;
        });

        return $container;
    }

    private function cleanupDefaultCommands(Application $application): void
    {
        $application->get('completion')->setHidden();
        $application->get('help')->setHidden();
    }
}
