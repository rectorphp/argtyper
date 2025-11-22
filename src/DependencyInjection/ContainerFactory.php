<?php

declare (strict_types=1);
namespace Rector\ArgTyper\DependencyInjection;

use Argtyper202511\Illuminate\Container\Container;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Rector\ArgTyper\Command\AddTypesCommand;
use Argtyper202511\Symfony\Component\Console\Application;
use Argtyper202511\Symfony\Component\Console\Input\ArrayInput;
use Argtyper202511\Symfony\Component\Console\Output\ConsoleOutput;
use Argtyper202511\Symfony\Component\Console\Output\NullOutput;
use Argtyper202511\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * @api
 */
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
        $container->singleton(SymfonyStyle::class, static function (): SymfonyStyle {
            // use null output ofr tests to avoid printing
            $consoleOutput = defined('PHPUNIT_COMPOSER_INSTALL') ? new NullOutput() : new ConsoleOutput();
            return new SymfonyStyle(new ArrayInput([]), $consoleOutput);
        });
        $container->singleton(Application::class, function (Container $container): Application {
            /** @var AddTypesCommand $addTypesCommand */
            $addTypesCommand = $container->make(AddTypesCommand::class);
            $application = new Application();
            $application->add($addTypesCommand);
            $this->hideDefaultCommands($application);
            return $application;
        });
        return $container;
    }
    /**
     * @see https://tomasvotruba.com/blog/how-make-your-tool-commands-list-easy-to-read
     */
    private function hideDefaultCommands(Application $application): void
    {
        $application->get('completion')->setHidden();
        $application->get('help')->setHidden();
    }
}
